<?php

declare(strict_types=1);

namespace Memed\Soluti\Receiver;

use Memed\Soluti\TestCase;

class DocumentSetTest extends TestCase
{
    /**
     * @dataProvider getDocuments
     */
    public function testIsWatingShouldCheckIfDocumentsIsNotSignedYet(bool $expected, array $documents)
    {
        $documentSet = new DocumentSet($documents);
        $this->assertEquals($expected, $documentSet->isWaiting());
    }

    public function getDocuments()
    {
        yield 'single document not signed' => [
            'expected' => true,
            'documents' => [
                new Document('WAITING', null),
            ],
        ];

        yield 'multiple documents not signed' => [
            'expected' => true,
            'documents' => [
                new Document('WAITING', null),
                new Document('WAITING', null),
                new Document('WAITING', null),
                new Document('WAITING', null),
            ],
        ];

        yield 'multiple signed documents and one not signed' => [
            'expected' => true,
            'documents' => [
                new Document('SIGNED', null),
                new Document('SIGNED', null),
                new Document('WAITING', null),
                new Document('SIGNED', null),
            ],
        ];

        yield 'single document signed' => [
            'expected' => false,
            'documents' => [
                new Document('SIGNED', null),
            ],
        ];

        yield 'multiple documents signed' => [
            'expected' => false,
            'documents' => [
                new Document('SIGNED', null),
                new Document('SIGNED', null),
                new Document('SIGNED', null),
                new Document('SIGNED', null),
            ],
        ];
    }
}
