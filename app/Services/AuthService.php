<?php

namespace App\Services;

use Bigcommerce\Api\Client as Bigcommerce;
use Bigcommerce\Api\Connection;
use Predis\Client as RedisClient;

class AuthService
{
    /** @var RedisClient */
    private $redis;

    public function __construct()
    {
        $this->redis = new RedisClient();
    }

    /**
     * @param $storeHash
     * @param $email
     * @return string
     */
    public function getUserKey($storeHash, $email): string
    {
        return "kitty.php:$storeHash:$email";
    }

    public function getBcAuthService(): string
    {
        $bcAuthService = getenv('BC_AUTH_SERVICE');

        return $bcAuthService ?: '';
    }

    /**
     * Assemble the payload to be sent to BC to get the access_token
     *
     * @param $code
     * @param $scope
     * @param $context
     * @return array
     */
    public function getAuthPayload(string $code, string $scope, string $context): array
    {
        return [
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'redirect_uri' => $this->getCallbackUrl(),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'scope' => $scope,
            'context' => $context,
        ];
    }

    /**
     * Returns a configured connection with BC.
     *
     * @param string $storeHash
     * @return Connection
     */
    public function getConfiguredBcClient(string $storeHash): Connection
    {
        $this->configureBCApi($storeHash);

        return Bigcommerce::getConnection();
    }

    /**
     * @param $signedRequest
     * @return array|null
     */
    public function verifySignedRequest($signedRequest): array
    {
        list($encodedData, $encodedSignature) = explode('.', $signedRequest, 2);
        $signature = base64_decode($encodedSignature);
        $jsonStr = base64_decode($encodedData);
        $data = json_decode($jsonStr, true);
        $expectedSignature = hash_hmac('sha256', $jsonStr, $this->getClientSecret(), $raw = false);
        if (!hash_equals($expectedSignature, $signature)) {
            error_log('Bad signed request from BigCommerce!');
            return null;
        }

        return $data;
    }

    /**
     * @return string Get the app's client ID from the environment vars
     */
    private function getClientId(): string
    {
        $clientId = getenv('BC_CLIENT_ID');

        return $clientId ?: '';
    }

    /**
     * @return string Get the callback URL from the environment vars
     */
    private function getCallbackUrl(): string
    {
        $callbackUrl = getenv('BC_CALLBACK_URL');

        return $callbackUrl ?: '';
    }

    private function getClientSecret(): string
    {
        $clientSecret = getenv('BC_CLIENT_SECRET');

        return $clientSecret ?: '';
    }

    /**
     * @param $storeHash
     * @return void
     */
    private function configureBcApi($storeHash): void
    {
        Bigcommerce::configure([
            'client_id' => $this->getClientId(),
            'auth_token' => $this->getAuthToken($storeHash),
            'store_hash' => $storeHash
        ]);
    }

    /**
     * @param $storeHash
     * @return string
     */
    private function getAuthToken($storeHash): string
    {
        $authData = json_decode($this->redis->get("stores/{$storeHash}/auth"));

        return $authData->access_token;
    }
}
