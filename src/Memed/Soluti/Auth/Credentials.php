<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

/**
 * This class is a simple DTO to handle user's credentials.
 */
class Credentials
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * Constructor.
     */
    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Retrieves username.
     */
    public function username(): string
    {
        return $this->username;
    }

    /**
     * Retrieves password.
     */
    public function password(): string
    {
        return $this->password;
    }
}
