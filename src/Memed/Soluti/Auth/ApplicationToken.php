<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class ApplicationToken implements AuthStrategy
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
     * @var string
     */
    protected $cloud;

    /**
     * Constructor.
     */
    public function __construct(
        string $token,
        string $type,
        string $cloud
    ) {
        $this->token = $token;
        $this->type = $type;
        $this->cloud = $cloud;
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

    /**
     * Retrieves cloud of token.
     */
    public function cloud(): string
    {
        return $this->cloud;
    }
}
