<?php

namespace App\Repositories;

use App\User;

use Auth0\Login\Auth0User;
use Auth0\Login\Auth0JWTUser;
use Auth0\Login\Repository\Auth0UserRepository;
use Illuminate\Contracts\Auth\Authenticatable;

class CustomUserRepository extends Auth0UserRepository
{
    protected function upsertUser($profile)
    {
        $user = User::where('sub', $profile['sub'])->first();
        if ($user == null) {
            $user = new User;
            $user->sub = $profile['sub'];
            $user->email = $profile['email'];
            $user->name = $profile['name'];
            $user->active = false;

            $pins = User::pluck('pin')->toArray();
            while (true) {
                $pin = rand(1000, 9999);
                if (in_array($pin, $pins)) {
                    continue;
                } else {
                    $user->pin = $pin;
                    break;
                }
            }

            $user->save();
        }
        return $user;
    }

    public function getUserByDecodedJWT(array $decodedJwt): Authenticatable
    {
        $user = $this->upsertUser($decodedJwt);
        return new Auth0JWTUser($user->getAttributes());
    }

    public function getUserByUserInfo(array $userinfo): Authenticatable
    {
        $user = $this->upsertUser($userinfo['profile']);
        return new Auth0User($user->getAttributes(), $userinfo['accessToken']);
    }
}
