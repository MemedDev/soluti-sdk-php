<?php

declare(strict_types=1);

namespace Memed\Soluti;

class DocumentTest extends TestCase
{
    /**
     * @var string
     */
    protected $filepath;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->filepath = __DIR__.'/fixtures/valid-file-to-sign.txt';
    }

    public function testFilenameShouldRetrieveNameProperly()
    {
        $document = new Document($this->filepath);

        $this->assertEquals('valid-file-to-sign.txt', $document->filename());
    }

    public function testFileShouldRetrieveAFileStreaming()
    {
        $document = new Document($this->filepath);

        $this->assertIsResource($document->file());
    }
}
