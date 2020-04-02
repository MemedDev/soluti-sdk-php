<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class CloudAuthentication
{
    public const CLOUD_NAME_VAULT_ID      = 'VAULT_ID';
    public const CLOUD_NAME_BIRD_ID       = 'BIRD_ID';
    public const CLOUD_USER_DISCOVERY_URL = '/v0/oauth/user-discovery';
    public const CLOUD_USER_DOCUMENT_TYPE = 'cpf';

    private $credentials;
    private $clouds;

    /**
     * CloudAuthentication constructor.
     *
     * @param  Credentials  $credentials
     * @param  array  $clouds
     * @throws \Exception
     */
    public function __construct(
        Credentials $credentials,
        array $clouds
    ) {
        $this->credentials = $credentials;

        $this->setClouds($clouds);
    }

    /**
     * @param  array  $clouds
     * @throws \Exception
     */
    public function setClouds(array $clouds): void
    {
        $this->clouds = [];

        foreach ($clouds as $cloud) {
            if (! $cloud instanceof Cloud) {
                throw new \Exception('Nuvem inválida.');
            }
            $this->clouds[$cloud->name()] = $cloud;
        }
    }

    /**
     * @param  array  $data
     * @return static
     * @throws \Exception
     */
    public static function create(array $data): self
    {
        return new self(
            $data['credentials'],
            $data['clouds']
        );
    }

    /**
     * @return array
     */
    public function clouds(): array
    {
        return $this->clouds;
    }

    /**
     * @param  string  $name
     * @return Cloud
     */
    public function cloud(string $name): Cloud
    {
        return $this->clouds[$name];
    }

    /**
     * @return bool
     */
    public function hasClouds(): bool
    {
        return !empty($this->clouds());
    }

    /**
     * @return Cloud
     */
    public function authenticatedClouds(): array
    {
        $authenticatedClouds = [];

        foreach ($this->clouds() as $cloud) {
            if ($cloud->discoveredOauthUser() && $cloud->discoveredOauthUser()->isValid()) {
                $authenticatedClouds[] = $cloud;
            }
        }

        return $authenticatedClouds;
    }

    /**
     * @return Credentials
     */
    public function credentials(): Credentials
    {
        return $this->credentials;
    }
}
