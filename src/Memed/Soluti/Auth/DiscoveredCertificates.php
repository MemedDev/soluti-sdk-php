<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class DiscoveredCertificates
{
    public $cloud;
    public $certificates = [];

    public function __construct(
        string $cloud,
        ?array $certificates = []
    ) {
        $this->cloud = $cloud;

        foreach ($certificates as $certificate) {
            $this->addCertificate($certificate);
        }
    }

    /**
     * @param Certificate $certificate
     */
    private function addCertificate(Certificate $certificate)
    {
        $this->certificates[] = $certificate;
    }

    /**
     * @param array $data
     * @return static
     */
    public static function create(array $data): self
    {
        if (isset($data['certificates'])) {
            foreach ($data['certificates'] as $key => $certificate) {
                $data['certificates'][$key] = Certificate::create(
                    array_merge($certificate, ['cloud' => $data['cloud']])
                );
            }
        }

        return new static(
            $data['cloud'] ?? null,
            $data['certificates'] ?? null
        );
    }

    /**
     * @return string
     */
    public function getCloud(): string
    {
        return $this->cloud;
    }

    /**
     * @return array
     */
    public function getCertificates(): array
    {
        return $this->certificates;
    }

    /**
     * @return Certificate|null
     */
    public function getValidCertificate(): ?Certificate
    {
        foreach ($this->getCertificates() as $certificate) {
            if ($certificate->isValid()) {
                return $certificate;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasValidCertificate(): bool
    {
        return !empty($this->getValidCertificate());
    }
}
