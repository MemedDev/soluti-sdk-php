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
    public const CLOUD_VAULT_ID = 'VAULT_ID';
    public const CLOUD_BIRD_ID = 'BIRD_ID';

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var UserDiscovery
     */
    protected $discovery;

    /**
     * Constructor.
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    protected function cloudUrl(string $cloud, ?string $endpoint): string
    {
        return $cloud === self::CLOUD_BIRD_ID
            ? $this->manager->birdIdUrl($endpoint)
            : $this->manager->vaultIdUrl($endpoint);
    }

    /**
     * Retrieves CloudAuthentication instance.
     *
     * @param  Credentials  $credentials
     * @return CloudAuthentication
     * @throws \Exception
     */
    public function cloudAuthentication(Credentials $credentials): CloudAuthentication
    {
        return new CloudAuthentication(
            $credentials,
            [
                CloudAuthentication::CLOUD_NAME_VAULT_ID => new Cloud(
                    CloudAuthentication::CLOUD_NAME_VAULT_ID,
                    $this->manager->vaultIdUrl(),
                    $this->manager->session()->applicationToken($credentials, CloudAuthentication::CLOUD_NAME_VAULT_ID)
                ),
                CloudAuthentication::CLOUD_NAME_BIRD_ID => new Cloud(
                    CloudAuthentication::CLOUD_NAME_BIRD_ID,
                    $this->manager->birdIdUrl(),
                    $this->manager->session()->applicationToken($credentials, CloudAuthentication::CLOUD_NAME_BIRD_ID)
                ),
            ]
        );
    }

    /**
     * Creates a new application session using given credentials.
     *
     * @param  Credentials  $credentials
     * @param  string  $cloud
     * @return ApplicationToken
     * @throws \Exception
     */
    public function applicationToken(Credentials $credentials, string $cloud): ApplicationToken
    {
        $endpoint = '/oauth/client_token';

        $payload = [
            'client_id' => $credentials->client()->id($cloud),
            'client_secret' => $credentials->client()->secret($cloud),
            'grant_type' => 'client_credentials',
            'lifetime' => $credentials->ttl(),
        ];

        try {
            $request = new Request(
                'post',
                $this->cloudUrl($cloud, $endpoint),
                $payload
            );

            $response = $this->manager->client()->json($request);
            $body = json_decode((string) $response->getBody(), true);

            return new ApplicationToken($body['access_token'], $body['token_type'], $cloud);

        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Check which in cloud the user has certificate
     *
     * @param  CloudAuthentication  $cloudAuthentication
     * @param  string|null  $document
     * @return UserDiscovery
     * @throws \Exception
     */
    public function userDiscovery(CloudAuthentication $cloudAuthentication, ?string $document = null): UserDiscovery
    {
        foreach ($cloudAuthentication->clouds() as $cloud) {
            $discovery = $this->userDiscoveryRequest($cloud->applicationToken(), $cloud->name(), $document);

            if (! $discovery->hasCertificate()) {
                continue;
            }

            return $discovery;
        }
    }

    /**
     * Check which in cloud the user has certificate
     *
     * @param  AuthStrategy  $token
     * @param  string|null  $document
     * @return UserDiscovery
     * @throws \Exception
     */
    public function userDiscoveryByToken(AuthStrategy $token, ?string $document = null): UserDiscovery
    {
        $discovery = $this->userDiscoveryRequest($token, self::CLOUD_VAULT_ID, $document);

        if (! $discovery->hasCertificate()) {
            $discovery = $this->userDiscoveryRequest($token, self::CLOUD_BIRD_ID, $document);
        }

        return $discovery;
    }

    /**
     * Send request to check which in cloud the user has certificate
     *
     * @param  AuthStrategy  $token
     * @param  string  $cloud
     * @param  string|null  $document
     * @return UserDiscovery
     * @throws \Exception
     */
    private function userDiscoveryRequest(AuthStrategy $token, string $cloud, ?string $document = null): UserDiscovery
    {
        $endpoint = $token instanceof ApplicationToken
            ? "/user-discovery?document={$document}"
            : "/user-discovery";

        $request = new Request('get', $this->cloudUrl($cloud, $endpoint), [], [
            'Authorization' => (string) $token,
        ]);

        $response = $this->manager->client()->json($request);

        return UserDiscovery::create(array_merge(
            [
                'cloud' => $cloud,
            ],
            json_decode((string) $response->getBody(), true)
        ));
    }

    /**
     * Creates a new session using given credentials.
     *
     * @param  Credentials  $credentials
     * @param  UserDiscovery  $userDiscovery
     * @return Token
     * @throws \Exception
     */
    public function create(Credentials $credentials, UserDiscovery $userDiscovery): Token
    {
        $endpoint = '/oauth';

        $request = new Request(
            'post',
            $this->cloudUrl($userDiscovery->getCloud(), $endpoint),
            [
                'client_id' => $credentials->client()->id($userDiscovery->getCloud()),
                'client_secret' => $credentials->client()->secret($userDiscovery->getCloud()),
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
