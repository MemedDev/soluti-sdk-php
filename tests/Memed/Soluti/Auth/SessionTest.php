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
    private $birdIdUrl  = 'http://birdid';
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
                'vaultid-secret'
            ),
            'username',
            'password',
            60
        );

        $this->userToken = new Token(
            'some-token',
            'some-type'
        );

        $this->applicationToken = new ApplicationToken(
            'some-token',
            'some-type',
            'BIRD_ID'
        );

        $this->cloudMock = m::mock(Cloud::class, [
            CloudAuthentication::CLOUD_NAME_BIRD_ID,
            $this->birdIdUrl,
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
                'url_birdid' => $this->birdIdUrl,
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
                'url_birdid' => $this->birdIdUrl,
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
                'url_birdid' => $this->birdIdUrl,
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
                'url_birdid' => $this->birdIdUrl,
            ]),
            $client
        );

        $userDiscoveryData = [
            'VAULT_ID' => [
                'cloud' => 'VAULT_ID',
                'name' => 'VAULT ID',
                'username' => $this->credentials->username(),
                'date_last_update' => '2020-03-17 18:45:00',
                'certificates' => [
                    [
                        'alias' => 'some-certificate',
                        'certificate' => '-----CERTIFICATE-CONTENT-----',
                        'issuerDN' => 'some-dn'
                    ]
                ],
                'detail' => [
                    'code' => 1109,
                    'status' => 'CERTIFICATES_LISTED',
                    'message' => 'Certificate Listing',
                ]
            ],
            'BIRD_ID' => [
                'cloud' => 'BIRD_ID',
                'name' => 'BIRD ID',
                'username' => $this->credentials->username(),
                'date_last_update' => '2020-03-17 18:45:00',
                'certificates' => [
                    [
                        'alias' => 'some-certificate',
                        'certificate' => '-----CERTIFICATE-CONTENT-----',
                        'issuerDN' => 'some-dn'
                    ]
                ],
                'detail' => [
                    'code' => 1109,
                    'status' => 'CERTIFICATES_LISTED',
                    'message' => 'Certificate Listing',
                ]
            ],
        ];

        $userDiscovery = [
            'VAULT_ID' => UserDiscovery::create($userDiscoveryData['VAULT_ID']),
            'BIRD_ID' => UserDiscovery::create($userDiscoveryData['BIRD_ID']),
        ];

        $userDiscoveryByTokenExpected = new UserDiscoveryByToken([
            CloudAuthentication::CLOUD_NAME_VAULT_ID => null,
            CloudAuthentication::CLOUD_NAME_BIRD_ID => null,
        ]);

        $userDiscoveryByTokenExpected->addData($userDiscovery['VAULT_ID']);
        $userDiscoveryByTokenExpected->addData($userDiscovery['BIRD_ID']);

        $session = new Session($manager);
        $response = m::mock(Response::class);

        $client->shouldReceive('json')
            ->with(m::on(function (Request $request) use ($response, $userDiscoveryData) {

                if ($request->getMethod() === 'GET' && (string) $request->getUri() === 'http://vaultid/user-discovery') {
                    $response->shouldReceive('getBody')
                        ->once()
                        ->andReturn(json_encode($userDiscoveryData['VAULT_ID']));

                    return true;
                }

                if ($request->getMethod() === 'GET' && (string) $request->getUri() === 'http://birdid/user-discovery') {
                    $response->shouldReceive('getBody')
                        ->once()
                        ->andReturn(json_encode($userDiscoveryData['BIRD_ID']));

                    return true;
                }
            }))
            ->andReturn($response);

        $userDiscoveryByToken = $session->userDiscoveryByToken($this->userToken);

        $this->assertEquals($userDiscoveryByTokenExpected, $userDiscoveryByToken);
        $this->assertTrue($userDiscoveryByToken->isAuthorized());
        $this->assertTrue($userDiscoveryByToken->isCertified());
    }

    public function testUserDiscoveryByApplicationToken()
    {
        $client = m::mock(HttpClient::class);

        $manager = new Manager(
            new Config([
                'url_vaultid' => $this->vaultIdUrl,
                'url_birdid' => $this->birdIdUrl,
            ]),
            $client
        );

        $userDiscoveryData = [
            'VAULT_ID' => [
                'cloud' => 'VAULT_ID',
                'name' => 'VAULT ID',
                'username' => $this->credentials->username(),
                'date_last_update' => '2020-03-17 18:45:00',
                'certificates' => [
                    [
                        'alias' => 'some-certificate',
                        'certificate' => '-----CERTIFICATE-CONTENT-----',
                        'issuerDN' => 'some-dn'
                    ]
                ],
                'detail' => [
                    'code' => 1109,
                    'status' => 'CERTIFICATES_LISTED',
                    'message' => 'Certificate Listing',
                ]
            ],
            'BIRD_ID' => [
                'cloud' => 'BIRD_ID',
                'name' => 'BIRD ID',
                'username' => $this->credentials->username(),
                'date_last_update' => '2020-03-17 18:45:00',
                'certificates' => [
                    [
                        'alias' => 'some-certificate',
                        'certificate' => '-----CERTIFICATE-CONTENT-----',
                        'issuerDN' => 'some-dn'
                    ]
                ],
                'detail' => [
                    'code' => 1109,
                    'status' => 'CERTIFICATES_LISTED',
                    'message' => 'Certificate Listing',
                ]
            ],
        ];

        $userDiscovery = [
            'VAULT_ID' => UserDiscovery::create($userDiscoveryData['VAULT_ID']),
            'BIRD_ID' => UserDiscovery::create($userDiscoveryData['BIRD_ID']),
        ];

        $userDiscoveryByTokenExpected = new UserDiscoveryByToken([
            CloudAuthentication::CLOUD_NAME_VAULT_ID => null,
            CloudAuthentication::CLOUD_NAME_BIRD_ID => null,
        ]);

        $userDiscoveryByTokenExpected->addData($userDiscovery['VAULT_ID']);
        $userDiscoveryByTokenExpected->addData($userDiscovery['BIRD_ID']);

        $session = new Session($manager);
        $response = m::mock(Response::class);

        $client->shouldReceive('json')
            ->with(m::on(function (Request $request) use ($response, $userDiscoveryData) {
                if ($request->getMethod() === 'GET' && (string) $request->getUri() === 'http://vaultid/user-discovery?document=username') {
                    $response->shouldReceive('getBody')
                        ->once()
                        ->andReturn(json_encode($userDiscoveryData['VAULT_ID']));

                    return true;
                }

                if ($request->getMethod() === 'GET' && (string) $request->getUri() === 'http://birdid/user-discovery?document=username') {
                    $response->shouldReceive('getBody')
                        ->once()
                        ->andReturn(json_encode($userDiscoveryData['BIRD_ID']));

                    return true;
                }
            }))
            ->andReturn($response);

        $userDiscoveryByToken = $session->userDiscoveryByToken($this->applicationToken, $this->credentials->username());

        $this->assertEquals($userDiscoveryByTokenExpected, $userDiscoveryByToken);
        $this->assertTrue($userDiscoveryByToken->isAuthorized());
        $this->assertTrue($userDiscoveryByToken->isCertified());
    }

    public function testUserDiscoveryByUnauthorizedUserToken()
    {
        $client = m::mock(HttpClient::class);

        $manager = new Manager(
            new Config([
                'url_vaultid' => $this->vaultIdUrl,
                'url_birdid' => $this->birdIdUrl,
            ]),
            $client
        );

        $userDiscoveryByTokenExpected = new UserDiscoveryByToken([
            CloudAuthentication::CLOUD_NAME_VAULT_ID => null,
            CloudAuthentication::CLOUD_NAME_BIRD_ID => null,
        ]);

        $userDiscoveryByTokenExpected->addError([
            'code' => 401,
            'cloud' => 'VAULT_ID',
            'message' => 'Client error: `GET https://apicloudid.hom.vaultid.com.br/user-discovery` resulted in a `401 Unauthorized` response',
        ]);

        $userDiscoveryByTokenExpected->addError([
            'code' => 401,
            'cloud' => 'BIRD_ID',
            'message' => 'Client error: `GET https://apihom.birdid.com.br/user-discovery` resulted in a `401 Unauthorized` response',
        ]);

        $session = new Session($manager);
        $response = m::mock(Response::class);

        $client->shouldReceive('json')
            ->with(m::on(function (Request $request) use ($response) {
                if ($request->getMethod() === 'GET' && (string) $request->getUri() === 'http://vaultid/user-discovery') {
                    $response->shouldReceive('getBody')
                        ->once()
                        ->andThrow(new \Exception(
                            'Client error: `GET https://apicloudid.hom.vaultid.com.br/user-discovery` resulted in a `401 Unauthorized` response',
                            401
                        ));

                    return true;
                }

                if ($request->getMethod() === 'GET' && (string) $request->getUri() === 'http://birdid/user-discovery') {
                    $response->shouldReceive('getBody')
                        ->once()
                        ->andThrow(new \Exception(
                            'Client error: `GET https://apihom.birdid.com.br/user-discovery` resulted in a `401 Unauthorized` response',
                            401
                        ));

                    return true;
                }
            }))
            ->andReturn($response);

        $userDiscoveryByToken = $session->userDiscoveryByToken($this->userToken);

        $this->assertEquals($userDiscoveryByTokenExpected, $userDiscoveryByToken);
        $this->assertFalse($userDiscoveryByToken->isAuthorized());
        $this->assertFalse($userDiscoveryByToken->isCertified());
    }

    public function testUserDiscoveryByBirdIdAuthorizedUserToken()
    {
        $client = m::mock(HttpClient::class);

        $manager = new Manager(
            new Config([
                'url_vaultid' => $this->vaultIdUrl,
                'url_birdid' => $this->birdIdUrl,
            ]),
            $client
        );

        $userDiscoveryData = [
            'BIRD_ID' => [
                'cloud' => 'BIRD_ID',
                'name' => 'BIRD ID',
                'username' => $this->credentials->username(),
                'date_last_update' => '2020-03-17 18:45:00',
                'certificates' => [
                    [
                        'alias' => 'some-certificate',
                        'certificate' => '-----CERTIFICATE-CONTENT-----',
                        'issuerDN' => 'some-dn'
                    ]
                ],
                'detail' => [
                    'code' => 1109,
                    'status' => 'CERTIFICATES_LISTED',
                    'message' => 'Certificate Listing',
                ]
            ],
        ];

        $userDiscovery = [
            'BIRD_ID' => UserDiscovery::create($userDiscoveryData['BIRD_ID']),
        ];

        $userDiscoveryByTokenExpected = new UserDiscoveryByToken([
            CloudAuthentication::CLOUD_NAME_VAULT_ID => null,
            CloudAuthentication::CLOUD_NAME_BIRD_ID => null,
        ]);

        $userDiscoveryByTokenExpected->addData($userDiscovery['BIRD_ID']);

        $userDiscoveryByTokenExpected->addError([
            'code' => 401,
            'cloud' => 'VAULT_ID',
            'message' => 'Client error: `GET https://apicloudid.hom.vaultid.com.br/user-discovery` resulted in a `401 Unauthorized` response',
        ]);

        $session = new Session($manager);
        $response = m::mock(Response::class);

        $client->shouldReceive('json')
            ->with(m::on(function (Request $request) use ($response, $userDiscoveryData) {

                if ($request->getMethod() === 'GET' && (string) $request->getUri() === 'http://vaultid/user-discovery') {
                    $response->shouldReceive('getBody')
                        ->once()
                        ->andThrow(new \Exception(
                            'Client error: `GET https://apicloudid.hom.vaultid.com.br/user-discovery` resulted in a `401 Unauthorized` response',
                            401
                        ));

                    return true;
                }

                if ($request->getMethod() === 'GET' && (string) $request->getUri() === 'http://birdid/user-discovery') {
                    $response->shouldReceive('getBody')
                        ->once()
                        ->andReturn(json_encode($userDiscoveryData['BIRD_ID']));

                    return true;
                }
            }))
            ->andReturn($response);

        $userDiscoveryByToken = $session->userDiscoveryByToken($this->userToken);

        $this->assertEquals($userDiscoveryByTokenExpected, $userDiscoveryByToken);
        $this->assertTrue($userDiscoveryByToken->isAuthorized());
        $this->assertTrue($userDiscoveryByToken->isCertified());
    }

    public function testUserDiscoveryByVaultIdAuthorizedUserToken()
    {
        $client = m::mock(HttpClient::class);

        $manager = new Manager(
            new Config([
                'url_vaultid' => $this->vaultIdUrl,
                'url_birdid' => $this->birdIdUrl,
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
                    'certificate' => '-----CERTIFICATE-CONTENT-----',
                    'issuerDN' => 'some-dn'
                ]
            ],
            'detail' => [
                'code' => 1109,
                'status' => 'CERTIFICATES_LISTED',
                'message' => 'Certificate Listing',
            ]
        ];

        $userDiscovery = UserDiscovery::create($userDiscoveryData);

        $userDiscoveryByTokenExpected = new UserDiscoveryByToken([
            CloudAuthentication::CLOUD_NAME_VAULT_ID => null,
            CloudAuthentication::CLOUD_NAME_BIRD_ID => null,
        ]);

        $userDiscoveryByTokenExpected->addData($userDiscovery);

        $userDiscoveryByTokenExpected->addError([
            'code' => 401,
            'cloud' => 'BIRD_ID',
            'message' => 'Client error: `GET https://apihom.birdid.com.br/user-discovery` resulted in a `401 Unauthorized` response',
        ]);

        $session = new Session($manager);
        $response = m::mock(Response::class);

        $client->shouldReceive('json')
            ->with(m::on(function (Request $request) use ($response, $userDiscoveryData) {

                if ($request->getMethod() === 'GET' && (string) $request->getUri() === 'http://vaultid/user-discovery') {
                    $response->shouldReceive('getBody')
                        ->once()
                        ->andReturn(json_encode($userDiscoveryData));

                    return true;
                }

                if ($request->getMethod() === 'GET' && (string) $request->getUri() === 'http://birdid/user-discovery') {
                    $response->shouldReceive('getBody')
                        ->once()
                        ->andThrow(new \Exception(
                            'Client error: `GET https://apihom.birdid.com.br/user-discovery` resulted in a `401 Unauthorized` response',
                            401
                        ));

                    return true;
                }
            }))
            ->andReturn($response);

        $userDiscoveryByToken = $session->userDiscoveryByToken($this->userToken);

        $this->assertEquals($userDiscoveryByTokenExpected, $userDiscoveryByToken);
        $this->assertTrue($userDiscoveryByToken->isAuthorized());
        $this->assertTrue($userDiscoveryByToken->isCertified());
    }

    public function testUserDiscoveryByVaultIdAuthorizedUserTokenWithoutCertificate()
    {
        $client = m::mock(HttpClient::class);

        $manager = new Manager(
            new Config([
                'url_vaultid' => $this->vaultIdUrl,
                'url_birdid' => $this->birdIdUrl,
            ]),
            $client
        );

        $userDiscoveryData = [
            'cloud' => 'VAULT_ID',
            'name' => 'VAULT ID',
            'username' => $this->credentials->username(),
            'date_last_update' => '2020-03-17 18:45:00',
            'certificates' => [],
            'detail' => [
                'code' => 1110,
                'status' => 'NO_CERTIFICATE_FOUND',
                'message' => 'No certificate found.',
            ]
        ];

        $userDiscovery = UserDiscovery::create($userDiscoveryData);

        $userDiscoveryByTokenExpected = new UserDiscoveryByToken([
            CloudAuthentication::CLOUD_NAME_VAULT_ID => null,
            CloudAuthentication::CLOUD_NAME_BIRD_ID => null,
        ]);

        $userDiscoveryByTokenExpected->addData($userDiscovery);

        $userDiscoveryByTokenExpected->addError([
            'code' => 401,
            'cloud' => 'BIRD_ID',
            'message' => 'Client error: `GET https://apihom.birdid.com.br/user-discovery` resulted in a `401 Unauthorized` response',
        ]);

        $session = new Session($manager);
        $response = m::mock(Response::class);

        $client->shouldReceive('json')
            ->with(m::on(function (Request $request) use ($response, $userDiscoveryData) {

                if ($request->getMethod() === 'GET' && (string) $request->getUri() === 'http://vaultid/user-discovery') {
                    $response->shouldReceive('getBody')
                        ->once()
                        ->andReturn(json_encode($userDiscoveryData));

                    return true;
                }

                if ($request->getMethod() === 'GET' && (string) $request->getUri() === 'http://birdid/user-discovery') {
                    $response->shouldReceive('getBody')
                        ->once()
                        ->andThrow(new \Exception(
                            'Client error: `GET https://apihom.birdid.com.br/user-discovery` resulted in a `401 Unauthorized` response',
                            401
                        ));

                    return true;
                }
            }))
            ->andReturn($response);

        $userDiscoveryByToken = $session->userDiscoveryByToken($this->userToken);

        $this->assertEquals($userDiscoveryByTokenExpected, $userDiscoveryByToken);
        $this->assertTrue($userDiscoveryByToken->isAuthorized());
        $this->assertFalse($userDiscoveryByToken->isCertified());
    }
}
