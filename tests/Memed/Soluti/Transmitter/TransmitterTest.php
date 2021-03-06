<?php

declare(strict_types=1);

namespace Memed\Soluti\Transmitter;

use GuzzleHttp\Psr7\Response;
use Memed\Soluti\Auth\Token as AuthToken;
use Memed\Soluti\Config;
use Memed\Soluti\Document;
use Memed\Soluti\Http\Client;
use Memed\Soluti\Http\Request;
use Memed\Soluti\Manager;
use Memed\Soluti\TestCase;
use Memed\Soluti\Transmitter\Token as TransactionToken;
use Mockery as m;

class TransmitterTest extends TestCase
{
    public function testTransmitShouldSendAFileUsingClient()
    {
        $cessUrl = 'http://cess';
        $client = m::mock(Client::class);
        $manager = new Manager(new Config(['url_cess' => $cessUrl]), $client);
        $transmitter = new Transmitter($manager);
        $authToken = new AuthToken('auth-token', 'bearer', 30, 'scope');
        $transactionToken = new TransactionToken('transaction-token');
        $transactionResponse = m::mock(Response::class);

        $authData = [
            'certificate_alias' => '',
            'type' => 'PDFSignature',
            'hash_algorithm' => 'SHA256',
            'auto_fix_document' => true,
            'documents_source' => 'UPLOAD_REFERENCE',
        ];

        $uploadData = [[
            'name' => 'document[0]',
            'contents' => 'some-file',
            'filename' => 'somefile.name',
        ]];

        $document = m::mock(Document::class);

        $document->shouldReceive('file')
            ->once()
            ->andReturn('some-file');

        $document->shouldReceive('filename')
            ->once()
            ->andReturn('somefile.name');

        $client->shouldReceive('json')
            ->with(m::on(function (Request $request) use ($authData, $authToken) {
                return (
                    $request->getMethod() === 'POST' &&
                    (string) $request->getUri() === 'http://cess/signature-service' &&
                    $request->getData() === $authData &&
                    $request->getHeader('Authorization') === [(string) $authToken]
                );
            }))
            ->once()
            ->andReturn($transactionResponse);

        $transactionResponse->shouldReceive('getBody')
            ->once()
            ->andReturn(json_encode([
                'tcn' => (string) $transactionToken,
            ]));

        $client->shouldReceive('multipart')
            ->with(m::on(function (Request $request) use ($transactionToken, $uploadData) {
                return (
                    $request->getMethod() === 'POST' &&
                    "http://cess/file-transfer/{$transactionToken}/eot" &&
                    $request->getData() === $uploadData
                );
            }))
            ->once();

        $this->assertEquals(
            $transactionToken,
            $transmitter->transmit($document, $authToken)
        );
    }
}
