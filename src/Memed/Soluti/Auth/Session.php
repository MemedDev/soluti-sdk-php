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
    private const USER_STATUS_CERTIFICATES_LISTED = 'CERTIFICATES_LISTED';

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

    private function cloudUrl(string $cloud, ?string $endpoint): string
    {
        return $cloud === 'birdId'
            ? $this->manager->birdIdUrl($endpoint)
            : $this->manager->vaultIdUrl($endpoint);
    }

    /**
     * Creates a new application session using given credentials.
     *
     * @param  Credentials  $credentials
     * @param  string  $cloud
     * @return Token
     */
    private function applicationToken(Credentials $credentials, string $cloud): Token
    {
        $endpoint = '/oauth/client_token';

        $payload = [
            'client_id' => $credentials->client()->id($cloud),
            'client_secret' => $credentials->client()->secret($cloud),
            'grant_type' => 'client_credentials',
            'lifetime' => $credentials->ttl(),
        ];

        $request = new Request(
            'post',
            $this->cloudUrl($cloud, $endpoint),
            $payload
        );

        return $this->parseResponse($this->manager->client()->json($request));
    }

    /**
     * Check which in cloud the user has certificate
     *
     * @param  Credentials  $credentials
     * @return mixed
     */
    public function userDiscovery(Credentials $credentials)
    {
        $discovery = $this->userDiscoveryRequest($credentials, 'vaultId');

        if ($discovery['detail']['status'] !== self::USER_STATUS_CERTIFICATES_LISTED) {
            $discovery = $this->userDiscoveryRequest($credentials, 'birdId');
        }

        return $discovery;
    }

    /**
     * Send request to check which in cloud the user has certificate
     *
     * @param  Credentials  $credentials
     * @param  string  $cloud
     * @return mixed
     */
    private function userDiscoveryRequest(Credentials $credentials, string $cloud)
    {
        $endpoint = "/user-discovery?document={$credentials->username()}";
        $applicationToken = $this->applicationToken($credentials, $cloud)->token();

        $request = new Request('get', $this->cloudUrl($cloud, $endpoint), [], [
            'Authorization' => "Bearer {$applicationToken}",
        ]);

        $response = $this->manager->client()->json($request);

        return array_merge([
            'cloud' => $cloud,
        ], json_decode((string) $response->getBody(), true));
    }

    /**
     * Creates a new session using given credentials.
     *
     * @param  Credentials  $credentials
     * @return Token
     */
    public function create(Credentials $credentials): Token
    {
        $discovery = $this->userDiscovery($credentials);

        $endpoint = '/oauth';

        $request = new Request(
            'post',
            $this->cloudUrl($discovery['cloud'], $endpoint),
            [
                'client_id' => $credentials->client()->id($discovery['cloud']),
                'client_secret' => $credentials->client()->secret($discovery['cloud']),
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
