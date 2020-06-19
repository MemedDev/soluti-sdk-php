<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

use DateInterval;
use DateTime;
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
     * @var UserDiscovery
     */
    protected $discovery;

    protected $cloudNames = [
        CloudAuthentication::CLOUD_NAME_VAULT_ID => null,
        CloudAuthentication::CLOUD_NAME_BIRD_ID => null,
    ];

    /**
     * Constructor.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function cloudUrl(string $cloud, ?string $endpoint): string
    {
        return $cloud === CloudAuthentication::CLOUD_NAME_BIRD_ID
            ? $this->manager->birdIdUrl($endpoint)
            : $this->manager->vaultIdUrl($endpoint);
    }

    /**
     * Creates a new application session using given credentials.
     *
     * @param Credentials $credentials
     * @param string      $cloud
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
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Retrieves CloudAuthentication instance.
     *
     * @param Credentials $credentials
     * @return CloudAuthentication
     * @throws \Exception
     */
    public function cloudAuthentication(Credentials $credentials): CloudAuthentication
    {
        $payload = [
            'credentials' => $credentials,
            'clouds' => [],
        ];

        foreach ($this->cloudNames as $cloudName => $cloud) {
            $authenticatedCloud = new Cloud(
                $cloudName,
                $this->cloudUrl($cloudName, null),
                $applicationToken = $this->manager->session()->applicationToken($credentials, $cloudName)
            );

            $authenticatedCloud->setDiscoveredOauthUser(
                $this->oauthUserDiscovery($authenticatedCloud, $credentials)
            );

            if ($authenticatedCloud->discoveredOauthUser()->isValid()) {
                $payload['clouds'][$cloudName] = $authenticatedCloud;
            }
        }

        return CloudAuthentication::create($payload);
    }

    /**
     * Checks in which cloud the user is registered
     *
     * @param Cloud       $cloud
     * @param Credentials $credentials
     * @return DiscoveredOauthUser
     */
    public function oauthUserDiscovery(Cloud $cloud, Credentials $credentials): DiscoveredOauthUser
    {
        return $this->oauthUserDiscoveryRequest(
            $cloud,
            $credentials
        );
    }

    /**
     * Send request to check which in cloud the user exists
     *
     * @param Cloud       $cloud
     * @param Credentials $credentials
     * @return DiscoveredOauthUser
     */
    private function oauthUserDiscoveryRequest(
        Cloud $cloud,
        Credentials $credentials
    ): DiscoveredOauthUser {
        $request = new Request(
            'post',
            $cloud->url(CloudAuthentication::CLOUD_USER_DISCOVERY_URL),
            [
                'client_id' => $credentials->client()->id($cloud->name()),
                'client_secret' => $credentials->client()->secret($cloud->name()),
                'user_cpf_cnpj' => CloudAuthentication::CLOUD_USER_DOCUMENT_TYPE,
                'val_cpf_cnpj' => $credentials->username(),
            ],
            [
                'Authorization' => (string) $cloud->applicationToken(),
            ]
        );

        $response = $this->manager->client()->json($request);

        return DiscoveredOauthUser::create(
            array_merge(
                ['cloud' => $cloud->name()],
                json_decode((string) $response->getBody(), true)
            )
        );
    }

    /**
     * Send request to check which in cloud the user has certificate
     *
     * @param Token $token
     * @param Cloud $cloud
     * @return DiscoveredCertificates
     */
    public function cessCertificateDiscovery(
        Token $token,
        Cloud $cloud
    ): DiscoveredCertificates {
        $request = new Request(
            'get',
            $this->manager->cessUrl(CloudAuthentication::CESS_CERTIFICATE_SERVICE_URL),
            [],
            [
                'Authorization' => $token->toVCSchema(),
            ]
        );

        $response = $this->manager->client()->json($request);

        return DiscoveredCertificates::create(
            array_merge(
                ['cloud' => $cloud->name()],
                json_decode((string) $response->getBody(), true)
            )
        );
    }

    /**
     * Check which in cloud the user has certificate
     *
     * @param CloudAuthentication $cloudAuthentication
     * @param string|null         $document
     * @return UserDiscovery
     * @throws \Exception
     */
    public function userDiscovery(CloudAuthentication $cloudAuthentication, ?string $document = null): UserDiscovery
    {
        foreach ($cloudAuthentication->clouds() as $cloud) {
            $discovery = $this->userDiscoveryRequest($cloud->applicationToken(), $cloud->name(), $document);

            if (!$discovery->hasCertificate()) {
                continue;
            }

            return $discovery;
        }
    }

    /**
     * Check which in cloud the user has certificate
     *
     * @param AuthStrategy $token
     * @param string|null  $document
     * @return UserDiscoveryByToken
     */
    public function userDiscoveryByToken(
        AuthStrategy $token,
        ?string $document = null
    ): UserDiscoveryByToken {
        $userDiscoveryByToken = new UserDiscoveryByToken($this->cloudNames);

        foreach ($this->cloudNames as $cloudName => $cloud) {
            try {
                $userDiscoveryByToken->addData(
                    $this->userDiscoveryRequest($token, $cloudName, $document)
                );
            } catch (\Exception $e) {
                $userDiscoveryByToken->addError(
                    [
                        'code' => $e->getCode(),
                        'cloud' => $cloudName,
                        'message' => $e->getMessage(),
                    ]
                );

                if ($e->getCode() === 401) {
                    continue;
                }
            }
        }

        return $userDiscoveryByToken;
    }

    /**
     * Send request to check which in cloud the user has certificate
     *
     * @param AuthStrategy $token
     * @param string       $cloud
     * @param string|null  $document
     * @return UserDiscovery
     * @throws \Exception
     */
    private function userDiscoveryRequest(AuthStrategy $token, string $cloud, ?string $document = null): UserDiscovery
    {
        $endpoint = $token instanceof ApplicationToken
            ? "/user-discovery?document={$document}"
            : "/user-discovery";

        $request = new Request(
            'get', $this->cloudUrl($cloud, $endpoint),
            [],
            [
                'Authorization' => (string) $token,
            ]
        );

        $response = $this->manager->client()->json($request);

        return UserDiscovery::create(
            array_merge(
                ['cloud' => $cloud],
                json_decode((string) $response->getBody(), true)
            )
        );
    }

    /**
     * Creates a new session using given credentials and cloud.
     *
     * @param Credentials $credentials
     * @param Cloud       $cloud
     * @param string      $certificateAlias
     * @return Token
     */
    public function create(
        Credentials $credentials,
        Cloud $cloud,
        string $certificateAlias
    ): Token {
        $request = new Request(
            'post',
            $this->cloudUrl($cloud->name(), CloudAuthentication::CLOUD_PWD_AUTHORIZE_URL),
            [
                'client_id' => $credentials->client()->id($cloud->name()),
                'client_secret' => $credentials->client()->secret($cloud->name()),
                'username' => $credentials->username(),
                'password' => $credentials->password(),
                'grant_type' => 'password',
                'scope' => 'signature_session',
                'lifetime' => $credentials->ttl(),
            ]
        );

        return $this->parseResponse(
            $this->manager->client()->json($request),
            $cloud->name(),
            $certificateAlias
        );
    }

    /**
     * Retrieves a token instance with response of session request.
     *
     * @param Response    $response
     * @param string      $cloud
     * @param string|null $certificateAlias
     * @return Token
     */
    protected function parseResponse(
        Response $response,
        string $cloud,
        string $certificateAlias = null
    ): Token {
        $data = json_decode((string) $response->getBody(), true);
        $expiresDatetime = (new DateTime())->add(new DateInterval("PT{$data['expires_in']}S"));

        return Token::create(
            [
                'cloud' => $cloud,
                'token' => $data['access_token'],
                'type' => $data['token_type'],
                'certificate_alias' => $certificateAlias,
                'expires_in' => $data['expires_in'],
                'expires_datetime' => $expiresDatetime,
                'scope' => $data['scope'],
                'slot_alias' => $data['slot_alias'],
            ]
        );
    }
}
