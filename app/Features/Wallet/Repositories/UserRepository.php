<?php

namespace App\Features\Wallet\Repositories;

use App\Features\Wallet\Models\User;

class UserRepository
{
    public function findByUuid($uuid): ?User
    {
        return User::query()->where('uuid', $uuid)->first();
    }

    public function findById($id): ?User
    {
        return User::query()->where('id', $id)->first();
    }
}
