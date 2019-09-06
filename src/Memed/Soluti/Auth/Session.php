<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

use GuzzleHttp\Psr7\Response;
use Memed\Soluti\Http\Request;
use Memed\Soluti\Manager;

/**
 * This class is responsible for handling session on Soluti's service.
 */
class Session
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
     * Creates a new session using given credentials.
     */
    public function create(Credentials $credentials): Token
    {
        $request = new Request(
            'post',
            $this->manager->vaultIdUrl('/oauth'),
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

        return $this->parseResponse($this->manager->client()->json($request));
    }

    /**
     * Retrieves a token instance with response of session request.
     */
    protected function parseResponse(Response $response): Token
    {
        $body = json_decode((string) $response->getBody(), true);

        return new Token($body['access_token'], $body['token_type']);
    }
}
