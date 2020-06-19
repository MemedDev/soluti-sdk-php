<?php

declare(strict_types=1);

namespace Memed\Soluti\Dto;

class SignaturePayload
{
    public const SIGNATURE_MODE_SYNC = 'sync';

    /**
     * @var string
     */
    public $certificate_alias;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $hash_algorithm;

    /**
     * @var bool
     */
    public $auto_fix_document;

    /**
     * @var array
     */
    public $signature_settings;

    /**
     * @var string
     */
    public $mode;

    /**
     * @var string
     */
    public $documents_source;

    /**
     * @var array
     */
    public $documents;

    public function __construct(
        string $certificate_alias,
        string $type,
        string $hash_algorithm,
        bool $auto_fix_document,
        array $signature_settings,
        string $mode,
        string $documents_source,
        array $documents
    ) {
        $this->certificate_alias = $certificate_alias;
        $this->type = $type;
        $this->hash_algorithm = $hash_algorithm;
        $this->auto_fix_document = $auto_fix_document;
        $this->signature_settings = $signature_settings;
        $this->mode = $mode;
        $this->documents_source = $documents_source;
        $this->documents = $documents;
    }

    public static function create(array $data): self
    {
        return new static(
            $data['certificate_alias'] ?? null,
            $data['type'] ?? 'PDFSignature',
            $data['hash_algorithm'] ?? 'SHA256',
            $data['auto_fix_document'] ?? true,
            $data['signature_settings'] ?? [],
            $data['mode'] ?? self::SIGNATURE_MODE_SYNC,
            $data['documents_source'] ?? 'DATA_URL',
            $data['documents'] ?? []
        );
    }

    public function __get(string $name)
    {
        return $this->{$name};
    }

    public function toArray(): array
    {
        return json_decode(json_encode($this), true);
    }
}