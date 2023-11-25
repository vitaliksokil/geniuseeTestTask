<?php

namespace App\Services\User;

use App\DTO\User\Profile\SaveEmailVerificationData;
use App\Http\Responses\User\Profile\SendEmailVerificationResponse;
use App\Mail\User\Auth\EmailVerificationMail;
use App\Models\User\GalleryItem;
use App\Models\User\User;
use App\Repositories\MongoDB\User\UserRepositoryInterface;
use App\Rules\PasswordRule;
use App\Rules\PhoneNumberRule;
use App\Rules\User\Gallery\GalleryItemRule;
use App\Rules\User\Gallery\GalleryStructureRule;
use App\Rules\User\UserOldPasswordMatchRule;
use App\Services\AdditionalServices\FileUploaderService\FileUploaderServiceInterface;
use App\Services\AdditionalServices\MailService\MailServiceInterface;
use App\Services\BaseService;
use App\Services\User\Auth\UserActivationService\UserActivationServiceInterface;
use App\Services\User\Gallery\GalleryItemServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserService extends BaseService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserActivationServiceInterface $userActivationService
    ) {
    }

    public function checkEmailExistence(string $email, string $ignoreUserId = null): bool
    {
        $data = ['email' => $email];
        $rules = ['required','email', Rule::unique('users', 'email')->when($ignoreUserId, function ($rule) use ($ignoreUserId){
            $rule->ignore($ignoreUserId, '_id');
        })];
        $this->validate($data, [
            'email' => $rules
        ]);
        return true;
    }

    public function checkPhoneExistence(array $phoneNumber, string $ignoreUserId = null): bool
    {
        $data = ['phone_number' => $phoneNumber];
        $rules = ['required', new PhoneNumberRule(), Rule::unique('users', 'phone_number')->when($ignoreUserId, function ($rule) use ($ignoreUserId){
            $rule->ignore($ignoreUserId, '_id');
        })];
        $this->validate($data, [
            'phone_number' => $rules
        ]);

        return true;
    }

    public function checkNicknameExistence(string $nickName, string $ignoreUserId = null): bool
    {
        $data = ['nickname' => $nickName];
        $rules = ['required', Rule::unique('users', 'nickname')->when($ignoreUserId, function ($rule) use ($ignoreUserId){
            $rule->ignore($ignoreUserId, '_id');
        })];
        $this->validate($data, [
            'nickname' => $rules
        ]);
        return true;
    }

    public function updateForgotPassword(User $user, array $data): bool
    {
        // just for now there is only password update via forgot password, later will be added other fields
        $this->validate($data, [
            'password' => ['required', 'string', 'confirmed', new PasswordRule()]
        ]);
        $data['password'] = Hash::make($data['password']);

        return $this->userRepository->updateProfile($user, $data);
    }

    public function updatePassword(User $user, array $data): User
    {
        $this->validate($data, [
            'old_password' => ['required', 'string', new UserOldPasswordMatchRule(auth()->user())],
            'new_password' => ['required', 'string', 'confirmed', new PasswordRule()]
        ]);
        $data['password'] = Hash::make($data['new_password']);

        $this->userRepository->updateProfile($user, $data);

        return $this->userRepository->getProfile($user);
    }


    public function updateProfile(User $user, array $data): User
    {
        convertEmptyStringToEmptyArrayByKey($data, 'tags');
        convertEmptyStringToEmptyArrayByKey($data, 'gallery');


        $this->validate($data, [
            'avatar' => (isset($data['avatar']) && is_file(
                    $data['avatar']
                )) ? 'nullable|image|max:10240' : 'nullable|string',
            'full_name' => 'nullable|string',
            'tags' => 'array|max:' . User::MAX_TAGS,
            'tags.*._id' => 'nullable|exists:tags,_id',
            'tags.*.title' => 'required',
            'website' => 'nullable|string',
            'about' => 'nullable',
            'gallery' => ['array', 'nullable', new GalleryStructureRule()],
            'gallery.*._id' => 'nullable|string',
            'gallery.*.media' => ['required', new GalleryItemRule()],
            'email' => ['string', 'email', Rule::unique('users', 'email')->ignore($user->id, '_id')],
            'phone_number' => ['unique:users,phone_number', new PhoneNumberRule()],
            'nickname' => [Rule::unique('users', 'nickname')->ignore($user->id, '_id')],
            'location' => '',
            'location.longitude' => isset($data['location']) ? 'required' : '',
            'location.latitude' => isset($data['location']) ? 'required' : '',
            'location.address' => isset($data['location']) ? 'required' : '',
        ]);

        $fileUploaderService = app()->make(FileUploaderServiceInterface::class);

        if (isset($data['email']) && !empty($data['email'])) {
            if ($user->email !== $data['email']) {
                $this->userActivationService->sendEmailVerification($user, $data['email']);
                $data['user_verified_at'] = null;
            }
        }

        if (isset($data['phone_number']) && !empty($data['phone_number'])) {
            return $this->updatePhone($user, $data);
        }

        if (isset($data['location']) && !empty($data['location'])) {
            $this->updateLocation($user, $data['location']);
            unset($data['location']);
        }


        $data = if_isset($data, 'avatar', function () use ($user, $data, $fileUploaderService) {
            if (is_file($data['avatar'])) {
                $data['avatar'] = $fileUploaderService->uploadOrUpdate(
                    file: $data['avatar'],
                    model: $user,
                    property: 'avatar',
                    path: (new User())->getTable() . '/' . $user->id,
                    filename: 'avatar'
                );
            } elseif (is_string($data['avatar'])) {
                unset($data['avatar']);
            }

            return $data;
        });

        $data = if_isset($data, 'gallery', function () use ($user, $data, $fileUploaderService) {
            /* @var GalleryItemServiceInterface $galleryItemService */
            $galleryItemService = app()->make(GalleryItemServiceInterface::class);
            $galleryItemService->uploadGallery(
                $user,
                (is_string($data['gallery']) && empty($data['gallery'])) ? [] : $data['gallery']
            );
            unset($data['gallery']);
            return $data;
        });

        $this->userRepository->updateProfile($user, $data);

        return $this->userRepository->getProfile($user);
    }

    public function deleteGalleryFile(User $user, GalleryItem $galleryItem): User
    {
        /* @var GalleryItemServiceInterface $galleryItemService */
        $galleryItemService = app()->make(GalleryItemServiceInterface::class);
        $galleryItemService->deleteGalleryItem($galleryItem);

        return $this->userRepository->getProfile($user);
    }

    public function updateEmail(User $user, array $data): User
    {
        $this->validate($data, [
            'email' => 'required|string|email|unique:users,email',
        ]);

        $this->updateEmailOrPhone($user, $data);

        return $this->userRepository->getProfile($user);
    }

    public function updatePhone(User $user, array $data): User
    {
        $this->validate($data, [
            'phone_number' => ['required', 'unique:users,phone_number', new PhoneNumberRule()],
        ]);

        $this->updateEmailOrPhone($user, $data);

        return $this->userRepository->getProfile($user);
    }

    public function updateLocation(User $user, array $data): User
    {
        $this->validate($data, [
            'longitude' => 'required',
            'latitude' => 'required',
            'address' => 'required',
        ]);

        // casting to float
        $data['longitude'] = (float)$data['longitude'];
        $data['latitude'] = (float)$data['latitude'];

        $this->userRepository->updateLocation($user, $data);

        return $this->userRepository->getProfile($user);
    }

    public function getAnotherUserProfile(string $nicknameOrId): User
    {
        return $this->userRepository->getAnotherUserProfile($nicknameOrId);
    }

    /*=== PRIVATE METHODS ===*/

    private function updateEmailOrPhone(User $user, array $data)
    {
        $userActivationService = app()->make(UserActivationServiceInterface::class);
        $data['user_id'] = $user->id;

        return $userActivationService->sendActivation($data);
    }

}
