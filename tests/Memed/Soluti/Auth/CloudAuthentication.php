<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

use Memed\Soluti\TestCase;

class CloudAuthenticationTest extends TestCase
{
    private $credentials;
    private $applicationToken;
    private $cloudAuthentication;
    private $cloudsArray;

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

        $this->applicationToken = new ApplicationToken(
            'some-token',
            'some-type',
            'VAULT_ID'
        );

        $this->cloudsArray = [
            CloudAuthentication::CLOUD_NAME_VAULT_ID => new Cloud(
                CloudAuthentication::CLOUD_NAME_VAULT_ID,
                'http://vaultid',
                $this->applicationToken
            ),
            CloudAuthentication::CLOUD_NAME_BIRD_ID => new Cloud(
                CloudAuthentication::CLOUD_NAME_BIRD_ID,
                'http://birdid',
                $this->applicationToken
            ),
        ];

        $this->cloudAuthentication = new CloudAuthentication(
            $this->credentials,
            $this->cloudsArray
        );
    }

    public function testCloudAuthenticationShouldRetrieveClouds()
    {
        $this->assertNotEmpty($this->cloudAuthentication->clouds());
        $this->assertEquals($this->cloudsArray, $this->cloudAuthentication->clouds());
    }

    public function testCloudAuthenticationShouldRetrieveSpecificCloud()
    {
        $this->assertNotEmpty($this->cloudAuthentication->cloud(CloudAuthentication::CLOUD_NAME_VAULT_ID));
        $this->assertEquals($this->cloudsArray, $this->cloudAuthentication->cloud(CloudAuthentication::CLOUD_NAME_VAULT_ID));
    }

    public function testCloudAuthenticationShouldHasClouds()
    {
        $this->assertTrue($this->cloudAuthentication->hasClouds());
    }
}
