<?php

namespace App\Http\Controllers;

use App\User;
use App\Auth\SSO;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RunetekSSOController extends Controller
{
    public function redirect(SSO $sso, Request $request)
    {
        return redirect($sso->redirectUrl());
    }

    public function callback(SSO $sso, Request $request)
    {
        $http = new Client();

        $token_response = $sso->exchangeAuthorizationCode($request->code);
        $token = $token_response['access_token'];
        $client = $sso->createApiClient($token);

        $user_response = json_decode((string) $client->get('user')->getBody(), true);

        $user = User::firstOrNew([
            'id' => $user_response['id'],
        ]);

        $props = [
            'name' => $user_response['name'],
            'created_at' => $user_response['created_at'],
            'token' => $token,
        ];

        $user->fill($props);
        $user->save();

        Auth::login($user, true);

        return redirect('/home');
    }

    private function getOauthConfig()
    {
        return config('services.runetek-sso');
    }
}
