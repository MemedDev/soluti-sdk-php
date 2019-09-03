<?php

declare(strict_types=1);

namespace Memed\Soluti\Receiver;

use Memed\Soluti\TestCase;

class DocumentTest extends TestCase
{
    public function testIsSignedShouldReturnTrueIfStatusCorrespondsToSigned()
    {
        $document = new Document('SIGNED', null);

        $this->assertTrue($document->isSigned());
    }

    public function testIsSignedShouldReturnFalseIfStatusIsNotCorrespondingToSigned()
    {
        $document = new Document('WAITING', null);

        $this->assertFalse($document->isSigned());
    }

    public function testNameShouldParseDocumentNameUsingItsLocationAndUseDefaultExtension()
    {
        $location = 'http://soluti/absolute/path/to/document/0';

        $document = new Document('SIGNED', $location);

        $this->assertEquals('document_0.pdf', $document->name());
    }

    public function testNameShouldParseDocumentNameUsingItsLocationAndUseCustomExtension()
    {
        $location = 'http://soluti/absolute/path/to/document/0';
        $extension = 'custom';

        $document = new Document('SIGNED', $location);

        $this->assertEquals('document_0.custom', $document->name($extension));
    }
}
