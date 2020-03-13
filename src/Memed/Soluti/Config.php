<?php

declare(strict_types=1);

namespace Memed\Soluti;

class Config
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Constructor.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Retrieves url of cess service.
     */
    public function cessUrl(): ?string
    {
        return $this->data['url_cess'];
    }

    /**
     * Retrieves url of vault id service.
     */
    public function vaultIdUrl(): ?string
    {
        return $this->data['url_vaultid'];
    }

    /**
     * Retrieves url of Bird Id service.
     */
    public function birdIdUrl(): ?string
    {
        return $this->data['url_birdid'];
    }
}
