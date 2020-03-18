<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class CloudAuthentication
{
    public const CLOUD_NAME_VAULT_ID = 'VAULT_ID';
    public const CLOUD_NAME_BIRD_ID = 'BIRD_ID';

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
    private function setClouds(array $clouds): void
    {
        foreach ($clouds as $cloud) {
            if (! $cloud instanceof Cloud) {
                throw new \Exception('Nuven inválida.');
            }
            $this->clouds[$cloud->name()] = $cloud;
        }
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
     * @return Credentials
     */
    public function credentials(): Credentials
    {
        return $this->credentials;
    }
}
