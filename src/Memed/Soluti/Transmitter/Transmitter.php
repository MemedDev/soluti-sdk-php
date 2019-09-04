<?php

declare(strict_types=1);

namespace Memed\Soluti\Transmitter;

use GuzzleHttp\Psr7\Response;
use Memed\Soluti\Auth\Token as AuthToken;
use Memed\Soluti\Document;
use Memed\Soluti\Http\Request;
use Memed\Soluti\Manager;

class Transmitter
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
     * Uploads a file to be signed.
     */
    public function transmit(Document $document, AuthToken $authToken): Token
    {
        $transactionToken = $this->start($authToken);

        $this->manager->client()->multipart(new Request(
            'post',
            $this->manager->cessUrl("/file-transfer/{$transactionToken}/eot"),
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
        return $this->parseReponse($this->manager->client()->json(
            new Request(
                'post',
                $this->manager->cessUrl('/signature-service'),
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
