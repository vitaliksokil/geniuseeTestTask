<?php

namespace App\Services\User\Auth\NearWalletAuthService;

use App\DTO\Auth\GetMessageData;
use App\DTO\Auth\NearWalletSignatureVerificationData;
use App\Http\Responses\User\Auth\AuthUserResponse;
use App\Models\User\Auth\NearNonceMessage;
use App\Repositories\MongoDB\User\Auth\NearNonceMessageRepository\NearNonceMessageRepositoryInterface;
use App\Repositories\MongoDB\User\UserRepositoryInterface;
use App\Repositories\NearRPC\User\AccountRpcRepository\AccountRpcRepositoryInterface;
use App\Repositories\NearRPC\User\Auth\NearRpcAuthRepositoryInterface;
use App\Services\BaseService;
use Elliptic\EdDSA;
use Illuminate\Support\Str;

class NearWalletAuthService extends BaseService implements NearWalletAuthServiceInterface
{
    public function __construct(
        private readonly NearRpcAuthRepositoryInterface $nearRpcAuthRepository,
        private readonly NearNonceMessageRepositoryInterface $nearNonceMessageRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly AccountRpcRepositoryInterface $accountRpcRepository,
    ) {
    }

    public function getAccountIdByPublicKey(array $data): string
    {
        $this->validate($data, [
            'public_key' => 'required|string'
        ]);

        $accountId = $this->nearRpcAuthRepository->getAccountIdByPublicKey($data['public_key']);

        if (is_null($accountId)) {
            $this->throwValidationError(['public_key' => [__('errors/validationCustomErrors.invalid_seed_phrase')]]);
        }

        return $accountId;
    }

    public function verifySignature(NearWalletSignatureVerificationData $data): AuthUserResponse
    {
        $nonceMessage = $this->nearNonceMessageRepository->getByAccountId($data->account_id);

        $result = $this->signatureCheck($data, $nonceMessage?->message)
            && $this->timestampCheck($nonceMessage) && $this->accessKeyCheck($data);

        if ($result) {
            $user = $this->userRepository->getByNearAccountId($data->account_id);
            if (!$user) {

                $userByNickname = $this->userRepository->getByNickname($data->account_id);

                $user = $this->userRepository->create(
                    array_merge($userByNickname ? [] : ['nickname' => $data->account_id], [
                        'near_account_id' => $data->account_id,
                    ])
                );
                $isFirstTime = true;
            }
            $this->nearNonceMessageRepository->deleteById($nonceMessage?->id);
            return new AuthUserResponse($user, $isFirstTime ?? false);
        } else {
            $this->throwValidationError(['signature' => [__('errors/validationCustomErrors.invalid_signature')]]);
        }
    }

    public function getMessage(GetMessageData $data): string
    {
        $message = $this->getNonce($data);
        $nearNonceMessage = $this->nearNonceMessageRepository->updateOrCreate($data->account_id, $message);
        return $nearNonceMessage->message;
    }

    private function getNonce(GetMessageData $data): string
    {
        $nonce = Str::random();
        $message = "Sign this message to confirm you own this wallet address. This action will not cost any gas fees.\n\nNonce: $nonce. \n\n Account Id: {$data->account_id}";
        return $message;
    }

    private function signatureCheck(NearWalletSignatureVerificationData $data, ?string $message): bool
    {
        $ec = new EdDSA('ed25519');
        $pubKey = bytesArrayToHex($data->public_key);
        $signature = bytesArrayToHex($data->signature);
        $key = $ec->keyFromPublic($pubKey);
        $msg = bin2hex($message);
        return $key->verify($msg, $signature);
    }

    private function timestampCheck(?NearNonceMessage $nearNonceMessage): bool
    {
        if ($nearNonceMessage){
            $updatedTime = $nearNonceMessage->updated_at;
            $currentTimestamp = \Carbon\Carbon::now();

            return $currentTimestamp->diffInSeconds($updatedTime) <= 10;
        }else{
            return false;
        }
    }

    private function accessKeyCheck(NearWalletSignatureVerificationData $data): bool
    {
        $result = $this->accountRpcRepository->getAccountAccessKeys($data->account_id, bytesToEd25519PubKey($data->public_key));

        if (isset($result['result']['error'])){
            return false;
        }else if (isset($result['result']['permission'])){
            return true;
        }else{
            return false;
        }
    }
}
