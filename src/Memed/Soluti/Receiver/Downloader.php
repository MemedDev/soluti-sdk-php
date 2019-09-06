<?php

declare(strict_types=1);

namespace Memed\Soluti\Receiver;

use Memed\Soluti\Http\Request;
use Memed\Soluti\Manager;
use Memed\Soluti\Receiver\DocumentSet;

class Downloader
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
     * Downloads at destination directory all documents in the given document
     * set.
     */
    public function download(DocumentSet $documentSet, string $destinationDir): array
    {
        return array_map(function (Document $document) use ($destinationDir) {
            $destination = $destinationDir . $document->name();

            $this->manager->client()->download(
                new Request('get', $document->location()),
                $destination
            );

            return $destination;
        }, $documentSet->getArrayCopy());
    }
}
