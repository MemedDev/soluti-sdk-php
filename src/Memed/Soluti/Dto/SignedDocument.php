<?php

declare(strict_types=1);

namespace Memed\Soluti\Dto;

class SignedDocument
{
    public const STATUS_SIGNED = 'SIGNED';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $original_file_name;

    /**
     * @var string
     */
    private $mediatype;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $lifetime;

    /**
     * @var string
     */
    private $result;

    /**
     * @var string
     */
    private $checksum;

    public function __construct(
        string $id,
        string $original_file_name,
        string $mediatype,
        string $status,
        int $lifetime,
        string $result,
        string $checksum
    ) {
        $this->id = $id;
        $this->original_file_name = $original_file_name;
        $this->mediatype = $mediatype;
        $this->status = $status;
        $this->lifetime = $lifetime;
        $this->result = $result;
        $this->checksum = $checksum;
    }

    public static function create(array $data): self
    {
        return new static(
            $data['id'] ?? null,
            $data['original_file_name'] ?? null,
            $data['mediatype'] ?? null,
            $data['status'] ?? null,
            $data['lifetime'] ?? null,
            $data['result'] ?? null,
            $data['checksum'] ?? null
        );
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOriginalFileName(): string
    {
        return $this->original_file_name;
    }

    /**
     * @return string
     */
    public function getMediatype(): string
    {
        return $this->mediatype;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getLifetime(): string
    {
        return $this->lifetime;
    }

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @return string
     */
    public function getChecksum(): string
    {
        return $this->checksum;
    }

    /**
     * @return bool
     */
    public function isSigned(): bool
    {
        return $this->status === self::STATUS_SIGNED;
    }
}