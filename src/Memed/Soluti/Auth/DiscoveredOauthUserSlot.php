<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class DiscoveredOauthUserSlot
{
    /**
     * @var string
     */
    private $slot_alias;

    /**
     * @var string
     */
    private $label;

    /**
     * DiscoveredOauthUserSlot constructor.
     *
     * @param  string  $slot_alias
     * @param  string  $label
     */
    public function __construct(
        string $slot_alias,
        string $label
    ) {
        $this->slot_alias = $slot_alias;
        $this->label = $label;
    }

    public static function create(array $data): self
    {
        return new self(
            $data['slot_alias'],
            $data['label']
        );
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
    public function label(): string
    {
        return $this->label;
    }

    public function toArray(): array
    {
        return [
            'slot_alias' => $this->slot_alias,
            'label' => $this->label,
        ];
    }
}
