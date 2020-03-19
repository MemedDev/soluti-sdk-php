<?php

declare(strict_types=1);

namespace Memed\Soluti;

use Memed\Soluti\Auth\ApplicationToken;
use Memed\Soluti\Auth\Cloud;
use Memed\Soluti\Auth\CloudAuthentication;
use Memed\Soluti\Auth\Credentials;
use Memed\Soluti\Auth\Session;
use Memed\Soluti\Auth\Token as AuthToken;
use Memed\Soluti\Auth\UserDiscovery;
use Memed\Soluti\Http\Client;
use Memed\Soluti\Receiver\DocumentSet;
use Memed\Soluti\Receiver\Downloader;
use Memed\Soluti\Receiver\Receiver;
use Memed\Soluti\TestCase;
use Memed\Soluti\Transmitter\Token as TransactionToken;
use Memed\Soluti\Transmitter\Transmitter;
use Mockery as m;

class SignerTest extends TestCase
{
    public function testSignShouldRetrieveFilesUsingAuthTokenStrategy()
    {
        $transmitter = m::mock(Transmitter::class);
        $receiver = m::mock(Receiver::class);
        $downloader = m::mock(Downloader::class);
        $manager = new Manager(
            m::mock(Config::class),
            m::mock(Client::class),
            $transmitter,
            $receiver,
            $downloader
        );

        $document = m::mock(Document::class);
        $authToken = m::mock(AuthToken::class);
        $destination = 'some/destination/directory/';

        $transactionToken = m::mock(TransactionToken::class);
        $documentSet = m::mock(DocumentSet::class);
        $files = [
            'some/destination/directory/file_1.pdf',
            'some/destination/directory/file_2.pdf',
            'some/destination/directory/file_3.pdf',
        ];

        $transmitter->shouldReceive('transmit')
            ->with($document, $authToken)
            ->once()
            ->andReturn($transactionToken);

        $receiver->shouldReceive('getDocuments')
            ->with($transactionToken)
            ->once()
            ->andReturn($documentSet);

        $downloader->shouldReceive('download')
            ->with($documentSet, $destination)
            ->once()
            ->andReturn($files);

        $this->assertEquals(
            $files,
            (new Signer($manager))->sign($document, $authToken, $destination)
        );
    }

    public function testSignShouldRetrieveFilesUsingCredentialsStrategy()
    {
        $transmitter = m::mock(Transmitter::class);
        $receiver = m::mock(Receiver::class);
        $downloader = m::mock(Downloader::class);
        $session = m::mock(Session::class);
        $credentials = new Credentials(
            new \Memed\Soluti\Auth\Client(
                '12345',
                'birdid-secret',
                '12345',
                'vaultid-secret',
            ),
            'username',
            'password',
            60
        );
        $userDiscovery = m::mock(UserDiscovery::class);
        $config = m::mock(Config::class);
        $config->shouldReceive('vaultIdUrl')->andReturn('https://vaultid');
        $config->shouldReceive('birdIdUrl')->andReturn('https://birdid');

        $manager = new Manager(
            $config,
            m::mock(Client::class),
            $transmitter,
            $receiver,
            $downloader,
            $session
        );

        $cloudAuthentication = m::mock(CloudAuthentication::class);

        $session->shouldReceive('cloudAuthentication')
            ->with($credentials)
            ->once()
            ->andReturn($cloudAuthentication);

        $session->shouldReceive('userDiscovery')
            ->with($cloudAuthentication, 'username')
            ->once()
            ->andReturn($userDiscovery);

        $document = m::mock(Document::class);
        $authToken = m::mock(AuthToken::class);
        $destination = 'some/destination/directory/';

        $transactionToken = m::mock(TransactionToken::class);
        $documentSet = m::mock(DocumentSet::class);
        $files = [
            'some/destination/directory/file_1.pdf',
            'some/destination/directory/file_2.pdf',
            'some/destination/directory/file_3.pdf',
        ];

        $session->shouldReceive('create')
            ->with($credentials, $userDiscovery)
            ->once()
            ->andReturn($authToken);

        $transmitter->shouldReceive('transmit')
            ->with($document, $authToken)
            ->once()
            ->andReturn($transactionToken);

        $receiver->shouldReceive('getDocuments')
            ->with($transactionToken)
            ->once()
            ->andReturn($documentSet);

        $downloader->shouldReceive('download')
            ->with($documentSet, $destination)
            ->once()
            ->andReturn($files);

        $this->assertEquals(
            $files,
            (new Signer($manager))->sign($document, $credentials, $destination)
        );
    }
}
