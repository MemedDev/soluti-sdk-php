<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class UserDiscoveryDetail
{
    private $code;
    private $status;
    private $message;

    public function __construct(
        int $code,
        string $status,
        string $message
    ) {
        $this->code = $code;
        $this->status = $status;
        $this->message = $message;
    }

    public static function create(array $data): self
    {
        return new self(
            $data['code'],
            $data['status'],
            $data['message']
        );
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

}
