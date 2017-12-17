<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class RunetekSSOController extends Controller
{
    public function redirect(Request $request)
    {
        $sso = $this->getOauthConfig();
        $query = http_build_query([
            'client_id' => $sso['client_id'],
            'redirect_uri' => url('oauth/callback'),
            'response_type' => 'code',
            'scope' => 'media:read media:write',
        ]);

        return redirect($sso['base_url'] . 'oauth/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        $http = new Client();

        $sso = $this->getOauthConfig();
        $response = $http->post($sso['base_url'] . 'oauth/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $sso['client_id'],
                'client_secret' => $sso['client_secret'],
                'redirect_uri' => url('oauth/callback'),
                'code' => $request->code,
            ],
        ]);

        return json_decode((string)$response->getBody(), true);
    }

    private function getOauthConfig()
    {
        return config('services.runetek-sso');
    }
}
