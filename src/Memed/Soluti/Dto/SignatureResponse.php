<?php

declare(strict_types=1);

namespace Memed\Soluti\Dto;

class SignatureResponse
{
    /**
     * @var string
     */
    protected $tcn;

    /**
     * @var string
     */
    protected $certificate_alias;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $hash_algorithm;

    /**
     * @var string
     */
    protected $checksum_algorithm;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var bool
     */
    protected $visible_signature;

    /**
     * @var bool
     */
    protected $tsa;

    /**
     * @var bool
     */
    protected $eot;

    /**
     * @var string
     */
    protected $documents_source;

    /**
     * @var SignedDocumentSet
     */
    protected $documents;

    /**
     * @var string|null
     */
    protected $transactionCookie;

    public function __construct(
        string $tcn,
        string $certificate_alias,
        string $type,
        string $hash_algorithm,
        string $checksum_algorithm,
        int $status,
        bool $visible_signature,
        bool $tsa,
        bool $eot,
        string $documents_source,
        SignedDocumentSet $documents,
        ?string $transactionCookie
    ) {
        $this->tcn = $tcn;
        $this->certificate_alias = $certificate_alias;
        $this->type = $type;
        $this->hash_algorithm = $hash_algorithm;
        $this->checksum_algorithm = $checksum_algorithm;
        $this->status = $status;
        $this->visible_signature = $visible_signature;
        $this->tsa = $tsa;
        $this->eot = $eot;
        $this->documents_source = $documents_source;
        $this->documents = $documents;
        $this->transactionCookie = $transactionCookie;
    }

    public static function create(array $data): self
    {
        return new static(
            $data['tcn'] ?? null,
            $data['certificate_alias'] ?? null,
            $data['type'] ?? null,
            $data['hash_algorithm'] ?? null,
            $data['checksum_algorithm'] ?? null,
            $data['status'] ?? null,
            $data['visible_signature'] ?? null,
            $data['tsa'] ?? null,
            $data['eot'] ?? null,
            $data['documents_source'] ?? null,
            $data['documents'] ?? null,
            $data['transactionCookie'] ?? null
        );
    }

    /**
     * @return string
     */
    public function getTcn(): string
    {
        return $this->tcn;
    }

    /**
     * @return string
     */
    public function getCertificateAlias(): string
    {
        return $this->certificate_alias;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getHashAlgorithm(): string
    {
        return $this->hash_algorithm;
    }

    /**
     * @return string
     */
    public function getChecksumAlgorithm(): string
    {
        return $this->checksum_algorithm;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isVisibleSignature(): bool
    {
        return $this->visible_signature;
    }

    /**
     * @return bool
     */
    public function isTsa(): bool
    {
        return $this->tsa;
    }

    /**
     * @return bool
     */
    public function isEot(): bool
    {
        return $this->eot;
    }

    /**
     * @return string
     */
    public function getDocumentsSource(): string
    {
        return $this->documents_source;
    }

    /**
     * @return SignedDocumentSet
     */
    public function getDocuments(): SignedDocumentSet
    {
        return $this->documents;
    }

    /**
     * @return string|null
     */
    public function getTransactionCookie(): ?string
    {
        return $this->transactionCookie;
    }
}