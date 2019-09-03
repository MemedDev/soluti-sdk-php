<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

use GuzzleHttp\Psr7\Response;
use Memed\Soluti\Http\Client as HttpClient;
use Memed\Soluti\Http\Request;
use Memed\Soluti\TestCase;
use Mockery as m;

class SessionTest extends TestCase
{
    public function testCreateShouldStartANewSessionAndGeneratAnAuthToken()
    {
        $credentials = new Credentials(
            new Client('12345', 'client-secret'),
            'username',
            'password',
            60
        );

        $requestBody = [
            'client_id' => $credentials->client()->id(),
            'client_secret' => $credentials->client()->secret(),
            'username' => $credentials->username(),
            'password' => $credentials->password(),
            'grant_type' => 'password',
            'scope' => 'signature_session',
            'lifetime' => $credentials->ttl(),
        ];

        $body = json_encode([
            'access_token' => 'some-token',
            'token_type' => 'some-type',
            'expires_in' => 30,
            'scope' => 'some-scope',
        ]);

        $client = m::mock(HttpClient::class);
        $session = new Session($client);
        $response = m::mock(Response::class);

        $client->shouldReceive('json')
            ->with(m::on(function (Request $request) use ($requestBody) {
                return (
                    $request->getMethod() === 'POST' &&
                    (string) $request->getUri() === 'https://apicloudid.hom.vaultid.com.br/oauth' &&
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

        $this->assertEquals($expected, $session->create($credentials));
    }
}
