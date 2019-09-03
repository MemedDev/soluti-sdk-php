<?php

declare(strict_types=1);

namespace Memed\Soluti\Transmitter;

use GuzzleHttp\Psr7\Response;
use Memed\Soluti\Auth\Token as AuthToken;
use Memed\Soluti\Document;
use Memed\Soluti\Http\Client;
use Memed\Soluti\Http\Request;

class Transmitter
{
    /**
     * Constructor.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Uploads a file to be signed.
     */
    public function transmit(Document $document, AuthToken $authToken): Token
    {
        $transactionToken = $this->start($authToken);

        $this->client->multipart(new Request(
            'post',
            "http://cess:8080/file-transfer/{$transactionToken}/eot",
            [
                [
                    'name' => 'document[0]',
                    'contents' => $document->file(),
                    'filename' => $document->filename(),
                ],
            ]
        ));

        return $transactionToken;
    }

    /**
     * Starts a new signature transaction for a single document.
     */
    protected function start(AuthToken $token)
    {
        return $this->parseReponse($this->client->json(
            new Request(
                'post',
                'http://cess:8080/signature-service',
                [
                    'certificate_alias' => '',
                    'type' => 'PDFSignature',
                    'hash_algorithm' => 'SHA256',
                    'auto_fix_document' => true,
                    'documents_source' => 'UPLOAD_REFERENCE',
                ],
                [
                    'Authorization' => (string) $token,
                ]
            )
        ));
    }

    /**
     * Retrieves a transaction token.
     */
    protected function parseReponse(Response $response): Token
    {
        $body = json_decode((string) $response->getBody(), true);

        return new Token($body['tcn']);
    }
}
