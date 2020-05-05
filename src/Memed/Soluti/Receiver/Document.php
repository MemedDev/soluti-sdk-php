<?php

declare(strict_types=1);

namespace Memed\Soluti\Receiver;

class Document
{
    public const STATUS_ERROR = 'ERROR';
    public const STATUS_SIGNED = 'SIGNED';

    /**
     * @var status
     */
    protected $status;

    /**
     * @var string
     */
    protected $location;

    /**
     * Constructor.
     */
    public function __construct(string $status, ?string $location)
    {
        $this->status = $status;
        $this->location = $location;
    }

    /**
     * Checks if an error was find when processing the document.
     */
    public function hasError(): bool
    {
        return $this->status === static::STATUS_ERROR;
    }

    /**
     * Checks if document is still waiting to be signed.
     */
    public function isSigned(): bool
    {
        return $this->status === static::STATUS_SIGNED;
    }

    /**
     * Retrieves file's location.
     */
    public function location(): string
    {
        return $this->location;
    }

    /**
     * Retrieves an unique name for document using it's location.
     */
    public function name(string $extension = 'pdf'): string
    {
        $path = explode('/', $this->location());
        $name = implode('_', array_slice($path, count($path) - 2));

        return sprintf('%s.%s', $name, $extension);
    }
}
