<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class UserDiscoveryByToken
{
    private $clouds = [];
    private $data = [];
    private $errors = [];

    public function __construct(
        ?array $clouds = [],
        ?array $data = [],
        ?array $errors = []
    ) {
        $this->clouds = $clouds;
        $this->errors = $errors;

        foreach ($data as $userDiscovery) {
            $this->addData($userDiscovery);
        }
    }

    public static function create(array $data): self
    {
        return new self(
            $data['clouds'] ?? [],
            $data['data'] ?? [],
            $data['errors'] ?? []
        );
    }

    public function addData(UserDiscovery $userDiscoveryRequest)
    {
        $this->data[] = $userDiscoveryRequest;
    }

    public function hasData(): bool
    {
        return !empty($this->data);
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getDataByCloud(string $cloud): ?UserDiscovery
    {
        $data = array_filter($this->data, function (UserDiscovery $userDiscovery) use ($cloud) {
            return $userDiscovery->getCloud() === $cloud;
        });

        return !empty($data) ? $data[0] : null;
    }

    public function getFirstData(): ?UserDiscovery
    {
        if (!empty($this->data)) {
            return $this->data[0];
        }

        return null;
    }

    public function addError(array $error)
    {
        $this->errors[] = $error;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

    private function getUnauthorizedErrors(): array
    {
        return array_filter($this->errors, function (array $error) {
            return $error['code'] === 401;
        });
    }

    public function isAuthorized(): bool
    {
        return !(count($this->getUnauthorizedErrors()) === count($this->clouds));
    }

    public function getCertified(): ?UserDiscovery
    {
        if (!empty($this->data)) {
            foreach ($this->data as $userDiscovery) {
                if ($userDiscovery->hasCertificate()) {
                    return $userDiscovery;
                }
            }
        }

        return null;
    }

    public function isCertified(): bool
    {
        return !empty($this->getCertified());
    }

}
