<?php

declare(strict_types=1);

namespace Memed\Soluti;

class Document
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var resource
     */
    protected $file;

    /**
     * Constructor.
     */
    public function __construct(string $path)
    {
        $this->parseFile($path);
    }

    /**
     * Retrieves filename.
     */
    public function filename(): string
    {
        return $this->filename;
    }

    /**
     * Retrieves file.
     */
    public function file()
    {
        return $this->file;
    }

    /**
     * Retrieves the content of given file path.
     */
    protected function parseFile(string $path): void
    {
        $this->filename = end(explode('/', $path));
        $this->file = fopen($path, 'r');
    }
}
