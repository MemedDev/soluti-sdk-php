<?php

declare(strict_types=1);

namespace Memed\Soluti\Receiver;

use Memed\Soluti\Http\Client;
use Memed\Soluti\Http\Request;
use Memed\Soluti\Receiver\DocumentSet;

class Downloader
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Constructor.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Downloads at destination directory all documents in the given document
     * set.
     */
    public function download(DocumentSet $documentSet, string $destinationDir): array
    {
        return array_map(function (Document $document) use ($destinationDir) {
            $destination = $destinationDir . $document->name();

            $this->client->download(
                new Request('get', $document->location()),
                $destination
            );

            return $destination;
        }, $documentSet->getArrayCopy());
    }
}
