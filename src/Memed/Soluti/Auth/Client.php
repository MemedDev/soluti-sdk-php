<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class Client
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $secret;

    /**
     * Constructor.
     */
    public function __construct(string $id, string $secret)
    {
        $this->id = $id;
        $this->secret = $secret;
    }

    /**
     * Retrieves client id.
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Retrieves client secret.
     */
    public function secret(): string
    {
        return $this->secret;
    }
}
