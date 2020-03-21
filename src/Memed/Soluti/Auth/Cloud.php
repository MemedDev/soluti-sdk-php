<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class Cloud
{
    private $name;
    private $url;
    private $applicationToken;
    private $discoveredOauthUser;

    public function __construct(
        string $name,
        string $url,
        ApplicationToken $applicationToken,
        ?DiscoveredOauthUser $discoveredOauthUser = null
    ) {
        $this->name = $name;
        $this->url = $url;
        $this->applicationToken = $applicationToken;
        $this->discoveredOauthUser = $discoveredOauthUser;
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

    /**
     * @return DiscoveredOauthUser|null
     */
    public function discoveredOauthUser(): ?DiscoveredOauthUser
    {
        return $this->discoveredOauthUser;
    }

    /**
     * @param  DiscoveredOauthUser|null  $discoveredOauthUser
     */
    public function setDiscoveredOauthUser(?DiscoveredOauthUser $discoveredOauthUser): void
    {
        $this->discoveredOauthUser = $discoveredOauthUser;
    }



}
