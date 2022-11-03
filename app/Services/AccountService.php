<?php

namespace App\Services;

use App\Models\Account;

class AccountService extends Service
{
    /**
     * @param int $id
     * @return Account|null
     */
    public static function find(int $id): ?Account
    {
        return null;
    }
}
