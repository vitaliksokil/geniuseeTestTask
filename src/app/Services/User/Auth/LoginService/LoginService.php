<?php

namespace App\Services\User\Auth\LoginService;

use App\DTO\Auth\FirebaseUser;
use App\Models\User\Auth\Provider;
use App\Repositories\MongoDB\User\Auth\LoginRepository\LoginRepositoryInterface;
use App\Rules\EmailOrPhoneRule;
use App\Rules\PasswordRule;
use App\Rules\User\UserByEmailOrPhoneExistsRule;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class LoginService extends BaseService implements LoginServiceInterface
{
    public function __construct(private readonly LoginRepositoryInterface $repository)
    {
    }

    public function login(array $data): array
    {
        $emailOrPhoneRule = new EmailOrPhoneRule();

        $this->validate($data,[
            'email_or_phone_number' => ['required', new UserByEmailOrPhoneExistsRule()],
            'password' => ['required', 'string', new PasswordRule()]
        ]);

        if ($emailOrPhoneRule->isEmail($data['email_or_phone_number'])){
            $loginData = $this->repository->loginViaEmail($data['email_or_phone_number'], $data['password']);
        }else{
            $loginData = $this->repository->loginViaSolidPhone($data['email_or_phone_number'], $data['password']);
        }


        if ($loginData){
            return $loginData;
        }else{
            $this->throwValidationError(['password' => [__('errors/validationCustomErrors.incorrect_password')]]);
        }
    }

    public function loginViaProvider(string $provider): string
    {
        $data = ['provider' => $provider];

        $this->validate($data,[
            'provider' => 'in:' . implode(',',Provider::ALLOWED_PROVIDERS),
        ]);

        return Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
    }

    public function loginViaProviderCallback(Request $request,string $provider): array
    {
        $data = array_merge($request->all(),['provider' => $provider]);

        $this->validate($data,[
            'provider'      => 'in:' . implode(',',Provider::ALLOWED_PROVIDERS),
            'uid'           => 'nullable',
            'token'         => 'nullable',
        ]);

        $socialiteUser = if_isset($data, 'uid',
            function () use ($data) {

//                try {
                    $auth = app('firebase.auth');

                    $signInResult = $auth->signInAsUser($data['uid']);

                    $verifiedIdToken = $auth->verifyIdToken($signInResult->data()['idToken']);

                    $authUser = $auth->getUser($verifiedIdToken->claims()->get('sub'));

                    $user = new FirebaseUser($authUser);

                    $socialiteUser = new SocialiteUser();
                    $socialiteUser->name = $user->getName();
                    $socialiteUser->nickname = $user->getNickname();
                    $socialiteUser->email = $user->getEmail();
                    $socialiteUser->avatar = $user->getAvatar();

                    return $socialiteUser;

//                } catch (\Exception $e) {
//                    throw new FirebaseIdTokenException('The token is invalid');
//                }

            },
            function () use ($data) {
                return Socialite::driver($data['provider'])->stateless()->user();
            });

        return $this->repository->loginViaProvider($data['provider'], $socialiteUser);
    }


}
