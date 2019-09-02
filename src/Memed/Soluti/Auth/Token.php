<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class Token
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
     * @var int
     */
    protected $expires_in;

    /**
     * @var string
     */
    protected $scope;

    /**
     * Constructor.
     */
    public function __construct(
        string $token,
        string $type,
        int $expires_in,
        string $scope
    ) {
        $this->token = $token;
        $this->type = $type;
        $this->expires_in = $expires_in;
        $this->scope = $scope;
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
     * Retrieves expires_in time.
     */
    public function expiresIn(): int
    {
        return $this->expires_in;
    }

    /**
     * Retrieves scope of token.
     */
    public function scope(): string
    {
        return $this->scope;
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
