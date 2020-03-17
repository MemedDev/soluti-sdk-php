<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

class Client
{
    /**
     * @var string
     */
    protected $birdid_id;

    /**
     * @var string
     */
    protected $birdid_secret;

    /**
     * @var string
     */
    protected $vaultid_id;

    /**
     * @var string
     */
    protected $vaultid_secret;

    /**
     * Constructor.
     *
     * @param  string  $birdid_id
     * @param  string  $birdid_secret
     * @param  string  $vaultid_id
     * @param  string  $vaultid_secret
     */
    public function __construct(
        string $birdid_id,
        string $birdid_secret,
        string $vaultid_id,
        string $vaultid_secret
    )
    {
        $this->birdid_id = $birdid_id;
        $this->birdid_secret = $birdid_secret;
        $this->vaultid_id = $vaultid_id;
        $this->vaultid_secret = $vaultid_secret;
    }

    private function formatCloudName(string $cloud): string
    {
        return strtolower(str_replace('_', '', $cloud));
    }

    public function id(string $cloud)
    {
        $attribute = $this->formatCloudName($cloud) . '_id';

        return $this->{$attribute};
    }

    public function secret(string $cloud)
    {
        $attribute = $this->formatCloudName($cloud) . '_secret';

        return $this->{$attribute};
    }

}
