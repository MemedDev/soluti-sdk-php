<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class Token implements AuthStrategy
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $type;

    /**
     * Constructor.
     */
    public function __construct(string $token, string $type)
    {
        $this->token = $token;
        $this->type = $type;
    }

    /**
     * Retrieves token string.
     */
    public function token(): string
    {
        return $this->token;
    }

    /**
     * Retrieves type of token.
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Retrieves token as formatted string.
     */
    public function __toString(): string
    {
        $type = ucfirst($this->type());

        return "{$type} {$this->token()}";
    }
}
