<?php

declare(strict_types=1);

namespace Memed\Soluti;

use App\Memed\JsonApi\Exception as JsonApiException;
use Memed\Soluti\Auth\AuthStrategy;
use Memed\Soluti\Auth\Credentials;
use Memed\Soluti\Auth\Token;

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
     * @see Memed\Soluti\Auth\AuthStrategy
     */
    public function sign(
        Document $document,
        AuthStrategy $token,
        string $destinationPath
    ): array {

        if ($token instanceof Credentials) {
            $userDiscovery = $this->manager->session()->userDiscoveryByCredentials($token);
            $token = $this->manager->session()->create($token, $userDiscovery);
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
}
