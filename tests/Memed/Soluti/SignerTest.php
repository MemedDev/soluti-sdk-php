<?php

declare(strict_types=1);

namespace Memed\Soluti;

use Memed\Soluti\Auth\Credentials;
use Memed\Soluti\Auth\Session;
use Memed\Soluti\Auth\Token as AuthToken;
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
        $manager = new Manager(
            m::mock(Config::class),
            m::mock(Client::class),
            $transmitter,
            $receiver,
            $downloader,
            $session
        );

        $document = m::mock(Document::class);
        $authToken = m::mock(AuthToken::class);
        $destination = 'some/destination/directory/';

        $credentials = m::mock(Credentials::class);
        $transactionToken = m::mock(TransactionToken::class);
        $documentSet = m::mock(DocumentSet::class);
        $files = [
            'some/destination/directory/file_1.pdf',
            'some/destination/directory/file_2.pdf',
            'some/destination/directory/file_3.pdf',
        ];

        $session->shouldReceive('create')
            ->with($credentials)
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
