<?php

declare(strict_types=1);

namespace Memed\Soluti\Http;

use GuzzleHttp\Psr7\Request as GuzzleRequest;

class Request extends GuzzleRequest
{
    protected const HEADER_DEFAULT = [
        'Accept' => 'application/json',
        'Cache-Control' => 'no-cache',
    ];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * Constructor.
     */
    public function __construct(
        string $method,
        string $uri,
        array $data = [],
        array $headers = []
    ) {
        parent::__construct($method, $uri, array_merge(
            static::HEADER_DEFAULT,
            $headers
        ));

        $this->data = $data;
    }

    /**
     * Retrieves request data.
     */
    public function getData(): array
    {
        return $this->data;
    }
}
