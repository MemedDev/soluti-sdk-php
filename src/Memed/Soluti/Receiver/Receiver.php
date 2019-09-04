<?php

declare(strict_types=1);

namespace Memed\Soluti\Receiver;

use GuzzleHttp\Psr7\Response;
use Memed\Soluti\Http\Request;
use Memed\Soluti\Manager;
use Memed\Soluti\Transmitter\Token;

class Receiver
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
     * Retrieves a set of parsed documents for given token transaction. It
     * attempts to get these documents sometimes because signature service
     * may have a delay to sign them.
     */
    public function getDocuments(Token $token, int $maxAttempts = 5, int $delay = 0): DocumentSet
    {
        do {
            $documentSet = $this->parseReponse($this->request($token));
            $attemps++;
            sleep($delay);
        } while ($documentSet->isWaiting() && $attemps < $maxAttempts);

        return $documentSet;
    }

    /**
     * Requests status of signature transaction.
     */
    protected function request(Token $token): Response
    {
        return $this->manager->client()->get(
            new Request(
                'get',
                $this->manager->cessUrl('/signature-service/'.(string) $token)
            )
        );
    }

    /**
     * Retrieves a set containing all documents in respective transaction.
     */
    protected function parseReponse(Response $response): DocumentSet
    {
        $body = json_decode((string) $response->getBody(), true);

        $documents = array_map(function (array $document) {
            return new Document($document['status'], $document['result']);
        }, $body['documents']);

        return new DocumentSet($documents);
    }
}
