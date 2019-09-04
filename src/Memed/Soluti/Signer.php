<?php

declare(strict_types=1);

namespace Memed\Soluti;

use Memed\Soluti\Auth\AuthStrategy;
use Memed\Soluti\Auth\Credentials;
use Memed\Soluti\Auth\Session;
use Memed\Soluti\Auth\Token;
use Memed\Soluti\Receiver\DocumentSet;
use Memed\Soluti\Receiver\Downloader;
use Memed\Soluti\Receiver\Receiver;
use Memed\Soluti\Transmitter\Transmitter;

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
     * @see Memed\Soluti\Auth\AuthStrategy
     */
    public function sign(
        Document $document,
        AuthStrategy $token,
        string $destinationPath
    ): array {
        if ($token instanceof Credentials) {
            $token = $this->manager->session()->create($token);
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
