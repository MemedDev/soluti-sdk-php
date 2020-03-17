<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class UserDiscovery
{
    private $cloud;
    private $detail;
    private $name;
    private $username;
    private $date_last_update;
    private $certificates;

    public function __construct(
        UserDiscoveryDetail $detail,
        ?string $cloud,
        ?string $name,
        ?string $username,
        ?string $date_last_update,
        ?array $certificates
    ) {
        $this->cloud = $cloud;
        $this->detail = $detail;
        $this->name = $name;
        $this->username = $username;
        $this->date_last_update = $date_last_update;
        $this->certificates = $certificates;
    }

    public static function create(array $data): self
    {
        return new self(
            UserDiscoveryDetail::create($data['detail']),
            $data['cloud'] ?? null,
            $data['name'] ?? null,
            $data['username'] ?? null,
            $data['date_last_update'] ?? null,
            $data['certificates'] ?? []
        );
    }

    public function hasCertificate(): bool
    {
        return $this->detail->getStatus() === 'CERTIFICATES_LISTED';
    }

    /**
     * @return string|null
     */
    public function getCloud(): ?string
    {
        return $this->cloud;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @return string|null
     */
    public function getDateLastUpdate(): ?string
    {
        return $this->date_last_update;
    }

    /**
     * @return array|null
     */
    public function getCertificates(): ?array
    {
        return $this->certificates;
    }

    /**
     * @return UserDiscoveryDetail
     */
    public function getDetail(): UserDiscoveryDetail
    {
        return $this->detail;
    }

}
