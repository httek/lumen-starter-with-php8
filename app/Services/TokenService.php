<?php

namespace App\Services;

use App\Models\User;
use App\Types\JsonWebTokenValidated;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;

class TokenService extends Service
{
    /**
     * @param User $user
     * @param int $ttl
     * @return string|null
     */
    public static function issue(User $user, int $ttl = 60): ?string
    {
        $facade = new JwtFacade();
        $token = $facade->issue(
            new Sha256(), InMemory::plainText(config('app.key')), static fn(
            Builder           $builder,
            DateTimeImmutable $issuedAt
        ): Builder => $builder
            ->issuedBy(config('app.url'))
            ->identifiedBy($user->getKey())
            ->permittedFor(config('app.url'))
            ->expiresAt($issuedAt->modify("+{$ttl} minutes"))
        );

        return $token->toString();
    }

    /**
     * @param string $sToken
     * @param array $constraints
     * @return JsonWebTokenValidated
     */
    public static function validate(string $sToken, array $constraints = []): JsonWebTokenValidated
    {
        $facade = new JwtFacade();
        $validated = new JsonWebTokenValidated();
        $constraints = array_merge([
            new IssuedBy(config('app.url')),
            new PermittedFor(config('app.url')),
        ], $constraints);

        try {
            $validated->setToken($facade->parse(
                $sToken,
                new SignedWith(new Sha256(), InMemory::plainText(config('app.key'))),
                new StrictValidAt(new FrozenClock(new DateTimeImmutable())),
                ...$constraints
            ));
        }

        // catch an a exception.
        catch (\Exception $e) {
            $message = $e->getMessage();
            if ($e instanceof RequiredConstraintsViolated) {
                $message = $e->violations()[0]->getMessage() ?? $message;
            }

            Log::withContext(['token' => $sToken])->error('TokenService.validate: ' . $message);
            $validated->setFail($message);
        }

        return $validated;
    }
}
