<?php

namespace App\Services\User\Auth\RegistrationService;

use App\Exceptions\API\User\Auth\AccountNotActivatedException;
use App\Repositories\MongoDB\User\Auth\RegistrationRepository\RegistrationRepositoryInterface;
use App\Repositories\MongoDB\User\Auth\UserActivationRepository\UserActivationRepositoryInterface;
use App\Rules\OnlyOneFieldOfTwoRequiredRule;
use App\Rules\PasswordRule;
use App\Rules\PhoneNumberRule;
use App\Services\BaseService;
use App\Services\User\Auth\UserActivationService\UserActivationServiceInterface;
use Illuminate\Support\Facades\Hash;

class RegistrationService extends BaseService implements RegistrationServiceInterface
{
    public function __construct(private readonly RegistrationRepositoryInterface   $registrationRepository,
                                private readonly UserActivationServiceInterface    $userActivationService,
                                private readonly UserActivationRepositoryInterface $userActivationRepository)
    {
    }

    public function registration(array $data): array
    {
        $this->validate($data, [
            'email' => 'string|email|unique:users,email|required_without:phone_number',
            'phone_number' => ['unique:users,phone_number',
                new PhoneNumberRule(),
                'required_without:email',
                new OnlyOneFieldOfTwoRequiredRule($data,'email')],
            'password' => ['required', 'string', 'confirmed', new PasswordRule()],
            'full_name' => ['required', 'string'],
            'nickname' => ['required', 'unique:users,nickname'],
            'birth_date' => ['date'],
            'longitude' => [],
            'latitude'  => [],
            'address'   => []
        ]);

        $data['password'] = Hash::make($data['password']);

        try {
            $userActivation = $this
                ->userActivationRepository
                ->getByEmailOrPhoneNumber($data['email'] ?? $data['phone_number']);
            $isActivated = $userActivation->status;
        }catch (\Exception $exception){
            $isActivated = false;
        }
        if ($isActivated) {
            if($this->userActivationService->deleteActivation($data)){
                $data['user_verified_at'] = now();
                return $this->registrationRepository->registration($data);
            }else{
                abort(500, 'Something went wrong');
            }
        }else{
            throw new AccountNotActivatedException();
        }
    }
}
