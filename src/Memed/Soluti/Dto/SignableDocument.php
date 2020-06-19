<?php

declare(strict_types=1);

namespace Memed\Soluti\Dto;

class SignableDocument
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $signature_setting;

    /**
     * @var string
     */
    private $original_file_name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $data;

    /**
     * SignableDocument constructor.
     *
     * @param string $id
     * @param string $signature_setting
     * @param string $original_file_name
     * @param string $path
     * @param string $data
     */
    public function __construct(
        string $id,
        string $signature_setting,
        string $original_file_name,
        string $path,
        string $data
    ) {
        $this->id = $id;
        $this->signature_setting = $signature_setting;
        $this->original_file_name = $original_file_name;
        $this->path = $path;
        $this->data = $data;
    }

    public static function create(array $data): self
    {
        $documentData = base64_encode(file_get_contents($data['path']));

        return new static(
            $data['id'] ?? null,
            $data['signature_setting'] ?? null,
            $data['original_file_name'] ?? null,
            $data['path'] ?? null,
            $data['data'] ?? "data:application/pdf;base64,{$documentData}"
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'signature_setting' => $this->signature_setting,
            'original_file_name' => $this->original_file_name,
            'path' => $this->path,
            'data' => $this->data,
        ];
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
    public function getSignatureSetting(): string
    {
        return $this->signature_setting;
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
    public function getData(): string
    {
        if (!empty($this->data)) {
            return $this->data;
        }

        $documentData = base64_encode(file_get_contents($this->getPath()));

        return "data:application/pdf;base64,{$documentData}";
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}