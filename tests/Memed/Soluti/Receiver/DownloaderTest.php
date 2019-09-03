<?php

declare(strict_types=1);

namespace Memed\Soluti\Receiver;

use Memed\Soluti\Http\Client;
use Memed\Soluti\Http\Request;
use Memed\Soluti\Receiver\Document;
use Memed\Soluti\Receiver\DocumentSet;
use Mockery as m;
use Memed\Soluti\TestCase;

class DownloaderTest extends TestCase
{
    public function testDownloadShouldProcessAllDocumentsInADocumentSet()
    {
        $client = m::mock(Client::class);
        $downloader = new Downloader($client);
        $destination = 'destination/dir/';

        $documentSet = new DocumentSet([
            new Document('SIGNED', 'location/document/0'),
            new Document('SIGNED', 'location/document/1'),
        ]);

        $client->shouldReceive('download')
            ->with(
                m::on(function (Request $request) {
                    return (string) $request->getUri() === 'location/document/0';
                }),
                'destination/dir/document_0.pdf'
            )
            ->once();

        $client->shouldReceive('download')
            ->with(
                m::on(function (Request $request) {
                    return (string) $request->getUri() === 'location/document/1';
                }),
                'destination/dir/document_1.pdf'
            )
            ->once();

        $expected = [
            'destination/dir/document_0.pdf',
            'destination/dir/document_1.pdf',
        ];

        $this->assertEquals(
            $expected,
            $downloader->download($documentSet, $destination)
        );
    }
}
