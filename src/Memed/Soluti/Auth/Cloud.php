<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class Cloud
{
    private $name;
    private $url;
    private $applicationToken;

    public function __construct(
        string $name,
        string $url,
        ApplicationToken $applicationToken
    ) {
        $this->name = $name;
        $this->url = $url;
        $this->applicationToken = $applicationToken;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param  string|null  $endpoint
     * @return string
     */
    public function url(?string $endpoint = ''): string
    {
        return $this->url.$endpoint;
    }

    /**
     * @return ApplicationToken
     */
    public function applicationToken(): ApplicationToken
    {
        return $this->applicationToken;
    }

}
