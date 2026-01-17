<?php

namespace App\Repositories\UserRepository;

use App\Models\User;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function getModel(): string
    {
        return User::class;
    }

    public function getInfoUser()
    {
        return auth()->user()->with('subscriptions');
    }

    public function getPermission()
    {
        $user = auth()->user();
        if (method_exists($user, 'getAllPermissions')) {
            return $user->getAllPermissions();
        }
        return null;
    }

    public function responseWithToken($token): array
    {
        return [
            'user' => auth()->user(),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth()->factory()->getTTL() * 60 * 24,
        ];
    }

    public function find($id)
    {
        return User::find($id);
    }

    public function findByEmail($email)
    {
        return User::query()->where('email', $email)->first();
    }

}
