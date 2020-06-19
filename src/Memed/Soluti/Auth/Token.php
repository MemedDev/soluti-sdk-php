<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

use DateTime;

class Token implements AuthStrategy
{
    /**
     * @var string
     */
    protected $cloud;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $certificate_alias;

    /**
     * @var int
     */
    protected $expires_in;

    /**
     * @var DateTime
     */
    protected $expires_datetime;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $slot_alias;

    /**
     * Constructor.
     *
     * @param string   $cloud
     * @param string   $token
     * @param string   $type
     * @param string   $certificate_alias
     * @param int      $expires_in
     * @param DateTime $expires_datetime
     * @param string   $scope
     * @param string   $slot_alias
     */
    public function __construct(
        string $cloud,
        string $token,
        string $type,
        string $certificate_alias,
        ?int $expires_in = null,
        ?DateTime $expires_datetime = null,
        ?string $scope = null,
        ?string $slot_alias = null
    ) {
        $this->cloud = $cloud;
        $this->token = $token;
        $this->type = $type;
        $this->certificate_alias = $certificate_alias;
        $this->expires_in = $expires_in;
        $this->expires_datetime = $expires_datetime;
        $this->scope = $scope;
        $this->slot_alias = $slot_alias;
    }

    /**
     * Make new Token instance from array
     *
     * @param array $data
     * @return static
     */
    public static function create(array $data): self
    {
        return new static(
            $data['cloud'] ?? null,
            $data['token'] ?? null,
            $data['type'] ?? null,
            $data['certificate_alias'] ?? null,
            $data['expires_in'] ?? null,
            $data['expires_datetime'] ?? null,
            $data['scope'] ?? null,
            $data['slot_alias'] ?? null
        );
    }

    /**
     * Retrieves token as formatted string.
     */
    public function __toString(): string
    {
        return $this->toBearer();
    }

    /**
     * Retrieves token as formatted VCSchema string.
     *
     * @return string
     */
    public function toVCSchema(): string
    {
        return 'VCSchema ' . base64_encode("{$this->cloud()}-|{$this->token()}");
    }

    /**
     * Retrieves token as formatted Bearer string.
     *
     * @return string
     */
    public function toBearer(): string
    {
        $type = ucfirst($this->type());

        return "{$type} {$this->token()}";
    }

    /**
     * Retrieves cloud string.
     */
    public function cloud(): string
    {
        return $this->cloud;
    }

    /**
     * Retrieves token string.
     */
    public function token(): string
    {
        return $this->token;
    }

    /**
     * Retrieves type of token.
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function expiresIn(): int
    {
        return $this->expires_in;
    }

    /**
     * @return string
     */
    public function scope(): string
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function slotAlias(): string
    {
        return $this->slot_alias;
    }

    /**
     * @return string
     */
    public function certificateAlias(): string
    {
        return $this->certificate_alias;
    }

    /**
     * @return array
     */
    public function toResponse(): array
    {
        return [
            'cloud'            => $this->cloud,
            'sign_token'       => $this->token,
            'token_type'       => $this->type,
            'expires_in'       => $this->expires_in,
            'expires_datetime' => $this->expires_datetime->format('Y-m-d H:i:s'),
        ];
    }
}