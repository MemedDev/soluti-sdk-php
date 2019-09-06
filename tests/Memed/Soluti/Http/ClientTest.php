<?php

declare(strict_types=1);

namespace Memed\Soluti\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use Memed\Soluti\TestCase;

class ClientTest extends TestCase
{
    public function testGetShouldSendAGetRequestToGuzzleUsingNoOptions()
    {
        $guzzle = m::mock(GuzzleClient::class);
        $client = new Client($guzzle);
        $response = m::mock(Response::class);

        $request = new Request('get', 'some-uri');

        $guzzle->shouldReceive('get')
            ->with($request->getUri(), ['headers' => $request->getHeaders()])
            ->once()
            ->andReturn($response);

        $this->assertEquals($response, $client->get($request));
    }

    public function testGetShouldSendAGetRequestToGuzzleMergingOptions()
    {
        $guzzle = m::mock(GuzzleClient::class);
        $client = new Client($guzzle);
        $response = m::mock(Response::class);
        $options = ['custom' => 'option'];

        $request = new Request('get', 'some-uri');

        $guzzle->shouldReceive('get')
            ->with($request->getUri(), [
                'headers' => $request->getHeaders(),
                'custom' => 'option',
            ])
            ->once()
            ->andReturn($response);

        $this->assertEquals($response, $client->get($request, $options));
    }

    public function testDownloadShouldSendAGetRequestToGuzzleUsingSaveTo()
    {
        $guzzle = m::mock(GuzzleClient::class);
        $client = new Client($guzzle);
        $response = m::mock(Response::class);
        $destination = 'some/directory';

        $request = new Request('get', 'some-uri');

        $guzzle->shouldReceive('get')
            ->with($request->getUri(), [
                'headers' => $request->getHeaders(),
                'save_to' => $destination,
            ])
            ->once()
            ->andReturn($response);

        $this->assertEquals(
            $response,
            $client->download($request, $destination)
        );
    }

    public function testJsonShouldSendARequestToGuzzleWithCorrectBody()
    {
        $guzzle = m::mock(GuzzleClient::class);
        $client = new Client($guzzle);
        $response = m::mock(Response::class);
        $body = [
            'some' => 'body',
            'json' => 'format',
        ];

        $request = new Request('post', 'some-uri', $body);

        $guzzle->shouldReceive('request')
            ->with(
                'POST',
                $request->getUri(),
                [
                    'headers' => $request->getHeaders(),
                    'json' => $body,
                ]
            )
            ->once()
            ->andReturn($response);

        $this->assertEquals($response, $client->json($request));
    }

    public function testMultipartShouldSendARequestToGuzzleWithCorrectBody()
    {
        $guzzle = m::mock(GuzzleClient::class);
        $client = new Client($guzzle);
        $response = m::mock(Response::class);
        $body = [
            'some' => 'body',
            'files' => 'to upload',
        ];

        $request = new Request('post', 'some-uri', $body);

        $guzzle->shouldReceive('request')
            ->with(
                'POST',
                $request->getUri(),
                [
                    'headers' => $request->getHeaders(),
                    'multipart' => $body,
                ]
            )
            ->once()
            ->andReturn($response);

        $this->assertEquals($response, $client->multipart($request));
    }
}
