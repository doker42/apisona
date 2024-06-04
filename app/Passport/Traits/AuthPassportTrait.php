<?php

namespace App\Passport\Traits;

use App\Passport\ClientAbsentException;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Client;
use stdClass;

trait AuthPassportTrait
{


    /**
     * @param string $clientName
     * @param string $email
     * @param string $password
     * @param bool|null $remember
     * @return stdClass
     * @throws ClientAbsentException
     */
    public static function getToken(string $clientName, string $email, string $password, ?bool $remember = false): stdClass
    {
        $result = new stdClass();
        $result->error = false;

        $data = self::getClientOAuth($clientName);

        $send = [
            'grant_type' => 'password',
            'client_id'  => $data->id,
            'client_secret' => $data->secret,
            'username' => $email,
            'password' => $password,
            'remember' => $remember,
            'scope'    => '',
        ];

        $response = Http::asForm()->post(env('APP_URL').'/oauth/token', $send);

        $resultData = $response->json();

        if (is_array($resultData) && isset($resultData['access_token'])) {
            $result->token = [
                'access_token'  => $resultData['access_token'],
                'refresh_token' => $resultData['refresh_token'],
                'expires_in'    => $resultData['expires_in'],
                'token_type'    => $resultData['token_type'],
            ];
        } else {
            $result->error = true;
            $result->error_message = $resultData['error'];
        }

        return $result;
    }

    /**
     * @param string $clientName
     * @param string $refreshToken
     * @return stdClass
     * @throws ClientAbsentException
     */
    public static function getRefreshToken(string $clientName, string $refreshToken): stdClass
    {
        $result = new stdClass();
        $result->error = false;
        $data = self::getClientOAuth($clientName);
        $response = Http::asForm()->post(env('APP_URL').'/oauth/token', [
            'grant_type' => 'refresh_token',
            'client_id'  => $data->id,
            'client_secret' => $data->secret,
            'refresh_token' => $refreshToken,
            'scope'      => '',
        ]);

        $resultData = $response->json();

        if (is_array($resultData) && isset($resultData['error'])) {
            $result->error = true;
        } else {
            $result->token = [
                'access_token'  => $resultData['access_token'],
                'expires_in'    => $resultData['expires_in'],
                'token_type'    => $resultData['token_type'],
                'refresh_token' => $resultData['refresh_token'],
            ];
        }

        return $result;
    }

    /**
     * @param string $tokenName
     * @return Client
     * @throws ClientAbsentException
     */
    public static function getClientOAuth(string $tokenName): Client
    {
        $client = Client::where([
            'name' => $tokenName,
            'password_client' => true,
            'revoked' => false
        ])->first();

        if (!$client) {
            throw new ClientAbsentException();
        }

        return $client;
    }
}
