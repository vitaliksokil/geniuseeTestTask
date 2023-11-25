<?php

namespace App\Services\User\Auth\UserActivationService;

use App\DTO\User\Profile\EmailVerificationData;
use App\DTO\User\Profile\SaveEmailVerificationData;
use App\Http\Responses\User\Profile\EmailVerificationResponse;
use App\Http\Responses\User\Profile\SendEmailVerificationResponse;
use App\Mail\User\Auth\ActivationCodeMail;
use App\Mail\User\Auth\EmailVerificationMail;
use App\Models\User\Auth\Activation\UserActivation;
use App\Models\User\User;
use App\Notifications\User\Auth\ActivationCodeSms;
use App\Repositories\MongoDB\User\Auth\UserActivationRepository\UserActivationRepositoryInterface;
use App\Repositories\MongoDB\User\UserRepositoryInterface;
use App\Rules\OnlyOneFieldOfTwoRequiredRule;
use App\Rules\PhoneNumberRule;
use App\Rules\User\Auth\Activation\ConfirmCodeRule;
use App\Services\AdditionalServices\CodeGeneratorService\CodeGeneratorServiceInterface;
use App\Services\AdditionalServices\MailService\MailServiceInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class UserActivationService extends BaseService implements UserActivationServiceInterface
{
    private array $rules;

    public function __construct(private readonly CodeGeneratorServiceInterface     $codeGeneratorService,
                                private readonly UserActivationRepositoryInterface $userActivationRepository,
                                private readonly UserRepositoryInterface           $userRepository)
    {
        $this->rules = [
            'email'         => 'required_without:phone_number|string|email|unique:users,email',
            'phone_number'  => ['required_without:email', 'unique:users,phone_number', new PhoneNumberRule()],
            'user_id'       => 'nullable|exists:users,_id'
        ];
    }

    public function sendActivation(array $data): bool
    {
        $this->rules['phone_number'][] = new OnlyOneFieldOfTwoRequiredRule($data, 'email');
        $this->validate($data, $this->rules);
        $data['code'] = $this->codeGeneratorService->generateCode(UserActivation::CODE_LENGTH);

        $result = $this->userActivationRepository->createOrUpdate($data);


        if ($result instanceof UserActivation) {
            if (isset($result->email)) {
                /* @var MailServiceInterface $mailService */
                $mailService = app()->make(MailServiceInterface::class);

                $mailService->send($data['email'], new ActivationCodeMail([
                    'code'      => $data['code'],
                    'user_id'   => $data['user_id'] ?? null
                ]));

            } elseif (isset($result->phone_number)) {
                Notification::route('sms', $result->getPhoneNumber())
                    ->notify(new ActivationCodeSms($data['code']));
            }
        }

        return (bool)$result;
    }

    public function confirmActivation(array $data): bool|User
    {

        $this->rules['email'] .= '|exists:user_activations,email'; // added rule to email
        $this->rules['phone_number'][] = new OnlyOneFieldOfTwoRequiredRule($data, 'email');

        $this->validate($data, array_merge($this->rules, [
            'code' => ['required', 'string', 'size:' . UserActivation::CODE_LENGTH, new ConfirmCodeRule($data['email'] ?? $data['phone_number'])],
        ]));

        $obj = $this->userActivationRepository->getByEmailOrPhoneNumber($data['email'] ?? $data['phone_number']);

        if ($obj->code == $data['code']) {

            // update user data
            if ($obj->user_id) {
                $this->deleteActivation($data);
                $this->userRepository->updateProfile($obj->user,$data);
                return $this->userRepository->getProfile($obj->user);
            }

            // update activation status
            return $this->userActivationRepository->updateStatus($obj->id, true);
        } else {
            return false;
        }
    }

    public function deleteActivation(array $data): bool
    {
        $this->rules['phone_number'][] = new OnlyOneFieldOfTwoRequiredRule($data, 'email');
        $this->validate($data, $this->rules);
        return $this->userActivationRepository->deleteActivation($data['email'] ?? $data['phone_number']);
    }

    public function saveVerification(SaveEmailVerificationData $data): UserActivation
    {
        return $this->userActivationRepository->createOrUpdate($data->toArray());
    }


    public function sendEmailVerification(User $user, string $email = null): SendEmailVerificationResponse
    {
        /* @var MailServiceInterface $mailService */
        $mailService = app()->make(MailServiceInterface::class);

        if (isset($user->email) || isset($email)){
            $userActivation = $this->saveVerification(new SaveEmailVerificationData([
                'email' => $email ?? $user->email,
                'user_id' => $user->id,
                'code' => Str::random()
            ]));

            $mailService->send($email ?? $user->email, new EmailVerificationMail([
                'code' => $userActivation->code
            ]));
            return new SendEmailVerificationResponse(true, $user->email);
        }else{
            return new SendEmailVerificationResponse(false, null);
        }
    }

    public function emailVerification(EmailVerificationData $data): EmailVerificationResponse
    {
        $userActivation = $this->userActivationRepository->getByCode($data->code);
        if (isset($userActivation)) {
            $this->userRepository->updateProfile($userActivation->user,['user_verified_at'=>now()]);
            $this->userActivationRepository->delete($userActivation);
            return new EmailVerificationResponse(true);
        }else{
            return new EmailVerificationResponse(false);
        }
    }

}
