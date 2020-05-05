<?php

declare(strict_types=1);

namespace Memed\Soluti\Receiver;

use GuzzleHttp\Psr7\Response;
use Memed\Soluti\Config;
use Memed\Soluti\Http\Client;
use Memed\Soluti\Http\Request;
use Memed\Soluti\Manager;
use Memed\Soluti\TestCase;
use Memed\Soluti\Transmitter\Token;
use Mockery as m;

class ReceiverTest extends TestCase
{
    public function testGetDocumentsShouldReturnSignedDocumentSetOnTheFirstAttempt()
    {
        $cessUrl = 'http://cess';
        $client = m::mock(Client::class);
        $manager = new Manager(new Config(['url_cess' => $cessUrl]), $client);
        $receiver = new Receiver($manager);
        $token = new Token('some-token', 'some-alias');
        $response = m::mock(Response::class);

        $body = json_encode([
            'documents' => [
                ['status' => 'SIGNED', 'result' => 'location/document/0'],
                ['status' => 'SIGNED', 'result' => 'location/document/1'],
            ],
        ]);

        $client->shouldReceive('get')
            ->with(m::on(function (Request $request) {
                return (string) $request->getUri() === 'http://cess/signature-service/some-token';
            }))
            ->once()
            ->andReturn($response);

        $response->shouldReceive('getBody')
            ->once()
            ->andReturn($body);

        $expected = new DocumentSet([
            new Document('SIGNED', 'location/document/0'),
            new Document('SIGNED', 'location/document/1'),
        ]);

        $this->assertEquals($expected, $receiver->getDocuments($token, 5, 0));
    }

    public function testGetDocumentsShouldReturnSignedDocumentSetOnTheLastAttempt()
    {
        $cessUrl = 'http://cess';
        $client = m::mock(Client::class);
        $manager = new Manager(new Config(['url_cess' => $cessUrl]), $client);
        $receiver = new Receiver($manager);
        $token = new Token('some-token', 'some-alias');
        $waitingResponse = m::mock(Response::class);
        $successResponse = m::mock(Response::class);

        $waitingBody = json_encode([
            'documents' => [
                ['status' => 'WAITING'],
                ['status' => 'WAITING'],
            ],
        ]);

        $successBody = json_encode([
            'documents' => [
                ['status' => 'SIGNED', 'result' => 'location/document/0'],
                ['status' => 'SIGNED', 'result' => 'location/document/1'],
            ],
        ]);

        $client->shouldReceive('get')
            ->with(m::on(function (Request $request) {
                return (string) $request->getUri() === 'http://cess/signature-service/some-token';
            }))
            ->times(5)
            ->andReturn(
                $waitingResponse,
                $waitingResponse,
                $waitingResponse,
                $waitingResponse,
                $successResponse
            );

        $waitingResponse->shouldReceive('getBody')
            ->times(4)
            ->andReturn($waitingBody);

        $successResponse->shouldReceive('getBody')
            ->once()
            ->andReturn($successBody);

        $expected = new DocumentSet([
            new Document('SIGNED', 'location/document/0'),
            new Document('SIGNED', 'location/document/1'),
        ]);

        $this->assertEquals($expected, $receiver->getDocuments($token, 5, 0));
    }

    public function testGetDocumentsShouldThrowExceptionAfterAllAttempts()
    {
        $cessUrl = 'http://cess';
        $client = m::mock(Client::class);
        $manager = new Manager(new Config(['url_cess' => $cessUrl]), $client);
        $receiver = new Receiver($manager);
        $token = new Token('some-token', 'some-alias');
        $response = m::mock(Response::class);

        $body = json_encode([
            'documents' => [
                ['status' => 'WAITING'],
                ['status' => 'WAITING'],
            ],
        ]);

        $client->shouldReceive('get')
            ->with(m::on(function (Request $request) {
                return (string) $request->getUri() === 'http://cess/signature-service/some-token';
            }))
            ->times(3)
            ->andReturn($response);

        $response->shouldReceive('getBody')
            ->times(3)
            ->andReturn($body);

        $expected = new DocumentSet([
            new Document('WAITING', null),
            new Document('WAITING', null),
        ]);

        $this->expectException(\Exception::class);

        $receiver->getDocuments($token, 3, 0);
    }

    public function testGetDocumentsShouldThrowExceptionWhenDocumentStatusIsError()
    {
        $cessUrl = 'http://cess';
        $client = m::mock(Client::class);
        $manager = new Manager(new Config(['url_cess' => $cessUrl]), $client);
        $receiver = new Receiver($manager);
        $token = new Token('some-token', 'some-alias');
        $response = m::mock(Response::class);

        $body = json_encode([
            'documents' => [
                ['status' => 'ERROR'],
                ['status' => 'WAITING'],
            ],
        ]);

        $client->shouldReceive('get')
            ->with(m::on(function (Request $request) {
                return (string) $request->getUri() === 'http://cess/signature-service/some-token';
            }))
            ->times(1)
            ->andReturn($response);

        $response->shouldReceive('getBody')
            ->times(1)
            ->andReturn($body);

        $expected = new DocumentSet([
            new Document('ERROR', null),
            new Document('WAITING', null),
        ]);

        $this->expectException(\Exception::class);

        $receiver->getDocuments($token, 3, 0);
    }
}
