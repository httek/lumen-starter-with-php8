<?php

namespace App\Types;

use Illuminate\Support\Str;
use Lcobucci\JWT\Token\Plain;

class JsonWebTokenValidated extends Type
{
    /**
     * @var bool
     */
    private bool $status = true;

    /**
     * @var string
     */
    private string $message = '';

    private ?Plain $token;

    /**
     * @return Plain|null
     */
    public function getToken(): ?Plain
    {
        return $this->token;
    }

    /**
     * @param Plain|null $token
     */
    public function setToken(?Plain $token): void
    {
        $this->token = $token;
    }

    /**
     * @param string $message
     * @return JsonWebTokenValidated
     */
    public function setFail(string $message): static
    {
        $this->status = false;
        $this->message = $message;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return Str::contains(
            $this->getMessage(), "token is expired"
        );
    }

    /**
     * @return bool
     */
    public function fails(): bool
    {
        return $this->status === false;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
