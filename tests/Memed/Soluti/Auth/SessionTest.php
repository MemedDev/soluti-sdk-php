<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

use GuzzleHttp\Psr7\Response;
use Memed\Soluti\Config;
use Memed\Soluti\Http\Client as HttpClient;
use Memed\Soluti\Http\Request;
use Memed\Soluti\Manager;
use Memed\Soluti\TestCase;
use Mockery as m;

class SessionTest extends TestCase
{
    private $credentials;
    private $birdidUrl  = 'http://birdid';
    private $vaultIdUrl = 'http://vaultid';
    private $applicationToken;
    private $userToken;
    private $cloudMock;
    private $discoveredOauthUserMock;
    private $discoveredOauthUserSlotMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->credentials = new Credentials(
            new Client(
                '12345',
                'birdid-secret',
                '12345',
                'vaultid-secret',
            ),
            'username',
            'password',
            60
        );

        $this->userToken = new Token(
            'some-token',
            'some-type',
        );

        $this->applicationToken = new ApplicationToken(
            'some-token',
            'some-type',
            'BIRD_ID'
        );

        $this->cloudMock = m::mock(Cloud::class, [
            CloudAuthentication::CLOUD_NAME_BIRD_ID,
            $this->birdidUrl,
            $this->applicationToken,
        ])
        ->makePartial();

        $this->discoveredOauthUserSlotMock = m::mock(DiscoveredOauthUserSlot::class, [
            'slot_alias_value',
            'label_value'
        ])
        ->makePartial();

        $this->discoveredOauthUserMock = m::mock(DiscoveredOauthUser::class, [
            'S',
            'slots' => [
                [
                    'slot_alias' => 'slot_alias_value',
                    'label' => 'label_value'
                ]
            ],
            CloudAuthentication::CLOUD_NAME_BIRD_ID,
        ])
        ->makePartial();
    }

    public function testCreateShouldStartANewSessionAndGenerateAnAuthToken()
    {
        $requestBody = [
            'client_id' => $this->credentials->client()->id($this->cloudMock->name()),
            'client_secret' => $this->credentials->client()->secret($this->cloudMock->name()),
            'username' => $this->credentials->username(),
            'password' => $this->credentials->password(),
            'grant_type' => 'password',
            'scope' => 'signature_session',
            'lifetime' => $this->credentials->ttl(),
        ];

        $body = json_encode([
            'access_token' => 'some-token',
            'token_type' => 'some-type',
            'expires_in' => 30,
            'scope' => 'some-scope',
        ]);

        $client = m::mock(HttpClient::class);

        $manager = new Manager(
            new Config([
                'url_vaultid' => $this->vaultIdUrl,
                'url_birdid' => $this->birdidUrl,
            ]),
            $client
        );

        $session = new Session($manager);
        $response = m::mock(Response::class);

        $client->shouldReceive('json')
            ->with(m::on(function (Request $request) use ($requestBody) {
                return (
                    $request->getMethod() === 'POST' &&
                    (string) $request->getUri() === 'http://birdid/oauth' &&
                    $request->getData() === $requestBody
                );
            }))
            ->once()
            ->andReturn($response);

        $response->shouldReceive('getBody')
            ->once()
            ->andReturn($body);

        $expected = new Token(
            'some-token',
            'some-type',
            30,
            'some-scope'
        );

        $this->assertEquals($expected, $session->create($this->credentials, $this->cloudMock));
    }

    public function testApplicationToken()
    {
        $requestBody = [
            'client_id' => $this->credentials->client()->id($this->cloudMock->name()),
            'client_secret' => $this->credentials->client()->secret($this->cloudMock->name()),
            'grant_type' => 'client_credentials',
            'lifetime' => $this->credentials->ttl(),
        ];

        $body = json_encode([
            'access_token' => 'some-token',
            'token_type' => 'some-type',
            'expires_in' => 30,
        ]);

        $client = m::mock(HttpClient::class);

        $manager = new Manager(
            new Config([
                'url_vaultid' => $this->vaultIdUrl,
                'url_birdid' => $this->birdidUrl,
            ]),
            $client
        );

        $session = new Session($manager);
        $response = m::mock(Response::class);

        $client->shouldReceive('json')
            ->with(m::on(function (Request $request) use ($requestBody) {
                return (
                    $request->getMethod() === 'POST' &&
                    (string) $request->getUri() === 'http://birdid/oauth/client_token' &&
                    $request->getData() === $requestBody
                );
            }))
            ->once()
            ->andReturn($response);

        $response->shouldReceive('getBody')
            ->once()
            ->andReturn($body);

        $expected = $this->applicationToken;

        $this->assertEquals($expected, $session->applicationToken($this->credentials, $this->cloudMock->name()));
    }

    public function testOauthUserDiscovery()
    {
        $client = m::mock(HttpClient::class);

        $manager = new Manager(
            new Config([
                'url_vaultid' => $this->vaultIdUrl,
                'url_birdid' => $this->birdidUrl,
            ]),
            $client
        );

        $discoveredOauthUserData = [
            'status' => 'S',
            'slots' => [
                [
                    'slot_alias' => 'slot_alias_value',
                    'label' => 'label_value'
                ]
            ],
            'cloud' => $this->cloudMock->name()
        ];

        $expected = DiscoveredOauthUser::create($discoveredOauthUserData);

        $response = m::mock(Response::class);
        $response->shouldReceive('getBody')
            ->once()
            ->andReturn(json_encode($discoveredOauthUserData));

        $session = new Session($manager);

        $requestBody = [
            'client_id' => $this->credentials->client()->id($this->cloudMock->name()),
            'client_secret' => $this->credentials->client()->secret($this->cloudMock->name()),
            'user_cpf_cnpj' => CloudAuthentication::CLOUD_USER_DOCUMENT_TYPE,
            'val_cpf_cnpj'  => 'username'
        ];

        $client->shouldReceive('json')
            ->with(m::on(function (Request $request) use ($requestBody) {
                return (
                    $request->getMethod() === 'POST' &&
                    (string) $request->getUri() === 'http://birdid/v0/oauth/user-discovery' &&
                    $request->getData() === $requestBody
                );
            }))
            ->once()
            ->andReturn($response);

        $this->assertEquals($expected, $session->oauthUserDiscovery($this->cloudMock, $this->credentials));
    }

    public function testUserDiscoveryByUserToken()
    {
        $client = m::mock(HttpClient::class);

        $manager = new Manager(
            new Config([
                'url_vaultid' => $this->vaultIdUrl,
                'url_birdid' => $this->birdidUrl,
            ]),
            $client
        );

        $userDiscoveryData = [
            'cloud' => 'VAULT_ID',
            'name' => 'VAULT ID',
            'username' => $this->credentials->username(),
            'date_last_update' => '2020-03-17 18:45:00',
            'certificates' => [
                [
                    'alias' => 'some-certificate',
                    'certificate' => '-----BEGIN CERTIFICATE-----\n-----END CERTIFICATE-----',
                    'issuerDN' => 'some-dn'
                ]
            ],
            'detail' => [
                'code' => 1109,
                'status' => 'CERTIFICATES_LISTED',
                'message' => 'Certificate Listing',
            ]
        ];

        $expected = UserDiscovery::create($userDiscoveryData);

        $response = m::mock(Response::class);
        $response->shouldReceive('getBody')
            ->once()
            ->andReturn(json_encode($userDiscoveryData));

        $session = new Session($manager);

        $client->shouldReceive('json')
            ->with(m::on(function (Request $request) {
                return (
                    $request->getMethod() === 'GET' &&
                    (string) $request->getUri() === 'http://vaultid/user-discovery'
                );
            }))
            ->once()
            ->andReturn($response);

        $this->assertEquals($expected, $session->userDiscoveryByToken($this->userToken));
    }

    public function testUserDiscoveryByApplicationToken()
    {
        $client = m::mock(HttpClient::class);

        $manager = new Manager(
            new Config([
                'url_vaultid' => $this->vaultIdUrl,
                'url_birdid' => $this->birdidUrl,
            ]),
            $client
        );

        $userDiscoveryData = [
            'cloud' => 'VAULT_ID',
            'name' => 'VAULT ID',
            'username' => $this->credentials->username(),
            'date_last_update' => '2020-03-17 18:45:00',
            'certificates' => [
                [
                    'alias' => 'some-certificate',
                    'certificate' => '-----BEGIN CERTIFICATE-----\n-----END CERTIFICATE-----',
                    'issuerDN' => 'some-dn'
                ]
            ],
            'detail' => [
                'code' => 1109,
                'status' => 'CERTIFICATES_LISTED',
                'message' => 'Certificate Listing',
            ]
        ];

        $expected = UserDiscovery::create($userDiscoveryData);

        $response = m::mock(Response::class);
        $response->shouldReceive('getBody')
            ->once()
            ->andReturn(json_encode($userDiscoveryData));

        $session = new Session($manager);

        $client->shouldReceive('json')
            ->with(m::on(function (Request $request) {
                return (
                    $request->getMethod() === 'GET' &&
                    (string) $request->getUri() === 'http://vaultid/user-discovery?document=username'
                );
            }))
            ->once()
            ->andReturn($response);

        $this->assertEquals($expected, $session->userDiscoveryByToken($this->applicationToken, $this->credentials->username()));
    }
}
