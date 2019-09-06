<?php

declare(strict_types=1);

namespace Memed\Soluti\Transmitter;

class Token
{
    /**
     * @var string
     */
    protected $token;

    /**
     * Constructor.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Retrieves token.
     */
    public function token(): string
    {
        return $this->token;
    }

    /**
     * Retrieves token object as string.
     */
    public function __toString(): string
    {
        return $this->token();
    }
}
