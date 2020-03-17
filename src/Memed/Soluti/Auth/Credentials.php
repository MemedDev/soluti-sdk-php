<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

/**
 * This class is a simple DTO to handle user's credentials.
 */
class Credentials implements AuthStrategy
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * Credentials constructor.
     *
     * @param  Client  $client
     * @param  string|null $username
     * @param  string|null $password
     * @param  int  $ttl
     */
    public function __construct(
        Client $client,
        string $username,
        string $password,
        int $ttl
    ) {
        $this->client = $client;
        $this->username = $username;
        $this->password = $password;
        $this->ttl = $ttl;
    }

    /**
     * Retrieves client.
     */
    public function client(): Client
    {
        return $this->client;
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

    /**
     * Retrieves time (in seconds) that credentials will be valid.
     */
    public function ttl(): int
    {
        return $this->ttl;
    }
}
