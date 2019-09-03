<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

use GuzzleHttp\Psr7\Response;
use Memed\Soluti\Http\Client as HttpClient;
use Memed\Soluti\Http\Request;

/**
 * This class is responsible for handling session on Soluti's service.
 */
class Session
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * Constructor.
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * Creates a new session using given credentials.
     */
    public function create(Credentials $credentials): Token
    {
        $request = new Request(
            'post',
            'https://apicloudid.hom.vaultid.com.br/oauth',
            [
                'client_id' => $credentials->client()->id(),
                'client_secret' => $credentials->client()->secret(),
                'username' => $credentials->username(),
                'password' => $credentials->password(),
                'grant_type' => 'password',
                'scope' => 'signature_session',
                'lifetime' => $credentials->ttl(),
            ]
        );

        return $this->parseResponse($this->client->json($request));
    }

    /**
     * Retrieves a token instance with response of session request.
     */
    protected function parseResponse(Response $response): Token
    {
        $body = json_decode((string) $response->getBody(), true);

        return new Token(
            $body['access_token'],
            $body['token_type'],
            $body['expires_in'],
            $body['scope']
        );
    }
}
