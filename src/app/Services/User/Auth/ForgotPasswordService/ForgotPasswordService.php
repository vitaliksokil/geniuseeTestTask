<?php

namespace App\Services\User\Auth\ForgotPasswordService;

use App\Mail\User\Auth\ForgotPasswordMail;
use App\Models\User\Auth\ForgotPassword;
use App\Models\User\User;
use App\Notifications\User\Auth\ActivationCodeSms;
use App\Repositories\MongoDB\User\Auth\ForgotPasswordRepository\ForgotPasswordRepositoryInterface;
use App\Repositories\MongoDB\User\UserRepositoryInterface;
use App\Rules\ExistsRule;
use App\Rules\OnlyOneFieldOfTwoRequiredRule;
use App\Rules\PasswordRule;
use App\Rules\PhoneNumberRule;
use App\Rules\User\Auth\ForgotPassword\VerifyCodeRule;
use App\Services\AdditionalServices\CodeGeneratorService\CodeGeneratorServiceInterface;
use App\Services\AdditionalServices\MailService\MailServiceInterface;
use App\Services\BaseService;
use App\Services\User\UserServiceInterface;
use Illuminate\Support\Str;

class ForgotPasswordService extends BaseService implements ForgotPasswordServiceInterface
{

    public function __construct(private readonly UserRepositoryInterface           $userRepository,
                                private readonly ForgotPasswordRepositoryInterface $repository,
                                private readonly CodeGeneratorServiceInterface     $codeGeneratorService,
                                private readonly UserServiceInterface              $userService)
    {
    }


    public function sendForgotPassword(array $data): string
    {
        $this->validate($data, [
            'email' => 'string|email|exists:users,email|required_without:phone_number',
            'phone_number' => [new ExistsRule(User::class,'phone_number'),
                new PhoneNumberRule(),
                'required_without:email',
                new OnlyOneFieldOfTwoRequiredRule($data, 'email')],
        ]);

        $token = Str::random(16);
        $code = $this->codeGeneratorService->generateCode(ForgotPassword::CODE_LENGTH);

        if (isset($data['email'])) {
            $user = $this->userRepository->getByEmail($data['email']);

            $this->repository->save([
                'code' => $code,
                'user_id' => $user->id,
                'token' => $token
            ]);

            /* @var MailServiceInterface $mailService */
            $mailService = app()->make(MailServiceInterface::class);

            $mailService->send($data['email'], new ForgotPasswordMail([
                'code' => $code,
                'user' => $user
            ]));

        } else {
            $user = $this->userRepository->getByPhone($data['phone_number']);

            $this->repository->save([
                'user_id' => $user->id,
                'code' => $code,
                'token' => $token
            ]);

            $user->notify(new ActivationCodeSms($code));

        }

        return $user->id;
    }

    public function verify(array $data): array
    {
        $this->validate($data, [
            'code' => [new VerifyCodeRule($data['user_id'] ?? '')],
            'user_id' => 'required_without:token|exists:forgot_passwords,user_id'
        ]);

        if (isset($data['token'])) {
            $forgotPassword = $this->repository->getByToken($data['token']);
        } else {
            $forgotPassword = $this->repository->getByCodeAndUserId($data['code'], $data['user_id']);
        }

        // returning token back to frontend (security for updating via sms)
        //  if there were no errors, for example, 422(incorrect user id and code) or 404(incorrect token)
        return [
            'token' => $forgotPassword->token,
            'user' => $forgotPassword->user->transform(['avatar'])->only('_id', 'full_name', 'avatar')
        ];
    }

    public function changePassword(array $data): bool
    {
        $this->validate($data, [
            'token' => 'required|exists:forgot_passwords,token',
            'password' => ['required', 'string', 'confirmed', new PasswordRule()],
            'password_confirmation' => ['required', new PasswordRule()]
        ]);

        $forgotPassword = $this->repository->getByToken($data['token']);

        $user = $forgotPassword->user;

        $forgotPassword->delete();

        return $this->userService->updateForgotPassword($user, $data);

    }
}
