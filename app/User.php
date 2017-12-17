<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    public $table = 'oauth_users';

    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'token',
    ];

    public function apiClient()
    {
        return new \GuzzleHttp\Client([
            'base_uri' => config('services.runetek-sso.base_url').'api',
            'headers' => [
                'Authorization' => 'Bearer '.$this->token,
            ],
        ]);
    }

    public function jars()
    {
        return $this->hasMany(Jar::class);
    }
}
