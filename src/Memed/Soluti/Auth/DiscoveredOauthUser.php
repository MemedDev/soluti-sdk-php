<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class DiscoveredOauthUser
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var array
     */
    private $slots = [];

    /**
     * @var string
     */
    private $cloud;

    /**
     * DiscoveredOauthUser constructor.
     *
     * @param  string  $status
     * @param  array  $slots
     * @param  string  $cloud
     */
    public function __construct(
        string $status,
        array $slots,
        string $cloud
    ) {
        $this->status = $status;

        foreach ($slots as $slot) {
            $this->slots[] = DiscoveredOauthUserSlot::create($slot);
        }

        $this->cloud = $cloud;
    }

    public static function create(array $data): self
    {
        return new self(
            $data['status'],
            $data['slots'],
            $data['cloud']
        );
    }

    /**
     * @return string
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function slots(): array
    {
        return $this->slots;
    }

    /**
     * @param  string  $document
     * @return DiscoveredOauthUserSlot
     */
    public function slot(string $document): ?DiscoveredOauthUserSlot
    {
        foreach ($this->slots as $slot) {
            if ($slot->slotAlias() === $document) {
                return $slot;
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return !empty($this->slots);
    }

    /**
     * @return string
     */
    public function cloud(): string
    {
        return $this->cloud;
    }

}
