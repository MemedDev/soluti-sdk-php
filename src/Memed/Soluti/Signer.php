<?php

declare(strict_types=1);

namespace Memed\Soluti;

use Memed\Soluti\Auth\AuthStrategy;
use Memed\Soluti\Auth\Cloud;
use Memed\Soluti\Auth\CloudAuthentication;
use Memed\Soluti\Auth\Credentials;
use Memed\Soluti\Auth\UserDiscovery;

class Signer
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Constructor.
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Sends given document object to be signed in Soluti service using given
     * strategy.
     *
     * @param  Document  $document
     * @param  AuthStrategy  $token
     * @param  string  $destinationPath
     * @return array
     * @throws \Exception
     * @see Memed\Soluti\Auth\AuthStrategy
     */
    public function sign(
        Document $document,
        AuthStrategy $token,
        string $destinationPath
    ): array {

        if ($token instanceof Credentials) {
            $credentials = $token;

            $token = $this->manager->session()->create(
                $credentials,
                $this->getUserDiscovery($credentials)
            );
        }

        $transactionToken = $this->manager
            ->transmitter()
            ->transmit($document, $token);

        $documents = $this->manager
            ->receiver()
            ->getDocuments($transactionToken);

        return $this->manager
            ->downloader()
            ->download($documents, $destinationPath);
    }

    /**
     * Get UserDiscovery instance
     *
     * @param  Credentials  $credentials
     * @return UserDiscovery
     * @throws \Exception
     */
    private function getUserDiscovery(Credentials $credentials): UserDiscovery
    {
        $clouds = $this->manager->session()->cloudAuthentication($credentials);

        return $this->manager->session()->userDiscovery($clouds, $credentials->username());
    }
}
