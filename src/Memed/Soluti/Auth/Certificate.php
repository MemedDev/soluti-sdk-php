<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class Certificate
{
    public $cloud;
    public $alias;
    public $detail;
    public $certificate;

    public function __construct(
        string $cloud,
        string $alias,
        array $detail,
        array $certificate
    ) {
        $this->cloud = $cloud;
        $this->alias = $alias;
        $this->detail = $detail;
        $this->certificate = $certificate;
    }

    public static function create(array $data): self
    {
        return new static(
            $data['cloud'] ?? null,
            $data['alias'] ?? null,
            $data['detail'] ?? null,
            $data['certificate'] ?? null
        );
    }

    public function isValid(): bool
    {
        if (!$this->detail['date']['status']) {
            return false;
        }

        if (!$this->detail['crl']['status']) {
            return false;
        }

        if (!$this->detail['trust']['status']) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getCloud(): string
    {
        return $this->cloud;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return array
     */
    public function getDetail(): array
    {
        return $this->detail;
    }

    /**
     * @return array
     */
    public function getCertificate(): array
    {
        return $this->certificate;
    }
}
