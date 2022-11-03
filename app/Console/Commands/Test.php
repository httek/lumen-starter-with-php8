<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\TokenService;
use Illuminate\Console\Command;

class Test extends Command
{
    protected $signature = 'test';

    public function handle()
    {
        $u = new User();
        $u->id = 10;
        $token = TokenService::issue($u, 1);

        dump($token);

        $validated = TokenService::validate($token);
        dd(
          $validated, $validated->isExpired(), $validated->getToken()->claims()->get('jti')
        );
    }
}
