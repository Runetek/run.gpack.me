<?php

namespace App\Auth;

use GuzzleHttp\Client;

class SSO
{
    /** @var array */
    private $config;

    public function __construct()
    {
        $this->config = config('services.runetek-sso');
    }

    public function config($key = null)
    {
        return array_get($this->config, $key);
    }

    public function createApiClient(string $token)
    {
        return new Client([
            'base_uri' => $this->config('base_url').'api/',
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);
    }

    public function exchangeAuthorizationCode(string $code)
    {
        $http = new Client();
        $response = $http->post($this->config('base_url').'oauth/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->config('client_id'),
                'client_secret' => $this->config('client_secret'),
                'redirect_uri' => url('oauth/callback'),
                'code' => $code,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function redirectUrl()
    {
        $query = http_build_query([
            'client_id' => $this->config('client_id'),
            'redirect_uri' => url('oauth/callback'),
            'response_type' => 'code',
            'scope' => 'media:read media:write',
        ]);

        return $this->config('base_url').'oauth/authorize?'.$query;
    }
}
