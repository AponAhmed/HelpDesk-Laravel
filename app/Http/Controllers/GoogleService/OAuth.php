<?php

namespace App\Http\Controllers\GoogleService;

use App\Http\Controllers\Controller;
use Google\Client;
use Google\Service\Gmail;
use Exception;

class OAuth extends Controller
{
    public array $token = [];
    public $client;
    public $tokenStatus = "default";
    public $redirectCode = false;
    public $connect;
    public $redirectUri = false;
    public $credentials;

    /**
     * Oauth Scope
     */
    public $scope;

    public function __construct(string $credentials, array $token = [], $scope = false)
    {
        $this->token = $token;
        if ($scope) {
            $this->scope = $scope;
        } else {
            $this->scope = Gmail::MAIL_GOOGLE_COM;
        }
        $this->credentials = json_decode(stripslashes($credentials), true);
        $this->createClient();
    }

    public function createClient()
    {
        $this->client = new Client();
        $this->client->setApplicationName("gmailClient");
        $this->client->setScopes($this->scope);
        $this->client->setAuthConfig($this->credentials);
        $this->client->setAccessType("offline");
        $this->client->setPrompt("select_account consent");

        if ($this->redirectUri) {
            $this->client->setRedirectUri($this->redirectUri); // Must Match with credential's redirect URL
        }
    }

    public function accesTokenByAuthCode($authCode)
    {
        try {
            $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
            $this->client->setAccessToken($accessToken);
            $this->token = $accessToken;
            $this->tokenStatus = "generated";
        } catch (Exception $e) {
            $this->tokenStatus = "generate-error";
        }
    }

    public function tokenCheck()
    {

        if (!empty($this->token)) {
            $this->client->setAccessToken($this->token);
        }

        if ($this->client->isAccessTokenExpired()) {
            $this->tokenStatus = "expired";
            if ($this->client->getRefreshToken()) {
                try {
                    $response = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    //var_dump($response);
                    if (isset($response['error'])) {
                        $this->tokenStatus = "refresh-response-error";
                        $this->connect = false;
                        return false;
                    } else {
                        $this->token = $response;
                        $this->tokenStatus = "refreshed";
                        $this->connect = true;
                        return true;
                    }
                } catch (Exception $e) {
                    $this->tokenStatus = "refresh-error";
                    $this->connect = false;
                    return false;
                }
            } else {
                $this->connect = false;
                return false;
            }
        } else {
            $this->tokenStatus = "not-expired";
            $this->connect = true;
            return true;
        }
    }

    public function login()
    {
        try {
            $link = $this->client->createAuthUrl();
            return $link;
        } catch (Exception $e) {
            return false;
        }
    }
}
