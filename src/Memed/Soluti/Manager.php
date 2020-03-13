<?php

declare(strict_types=1);

namespace Memed\Soluti;

use GuzzleHttp\Client as GuzzleClient;
use Memed\Soluti\Auth\Session;
use Memed\Soluti\Http\Client;
use Memed\Soluti\Receiver\Downloader;
use Memed\Soluti\Receiver\Receiver;
use Memed\Soluti\Transmitter\Transmitter;

class Manager
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Transmitter
     */
    protected $transmitter;

    /**
     * @var Receiver
     */
    protected $receiver;

    /**
     * @var Downloader
     */
    protected $downloader;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Constructor.
     */
    public function __construct(
        Config $config,
        Client $client = null,
        Transmitter $transmitter = null,
        Receiver $receiver = null,
        Downloader $downloader = null,
        Session $session = null
    ) {
        $this->config = $config;
        $this->client = $client ?: new Client(new GuzzleClient());
        $this->transmitter = $transmitter ?: new Transmitter($this);
        $this->receiver = $receiver ?: new Receiver($this);
        $this->downloader = $downloader ?: new Downloader($this);
        $this->session = $session ?: new Session($this);
    }

    /**
     * Retrieves client object.
     */
    public function client(): Client
    {
        return $this->client;
    }

    /**
     * Retrieves transmitter object.
     */
    public function transmitter(): Transmitter
    {
        return $this->transmitter;
    }

    /**
     * Retrieves receiver object.
     */
    public function receiver(): Receiver
    {
        return $this->receiver;
    }

    /**
     * Retrieves downloader object.
     */
    public function downloader(): Downloader
    {
        return $this->downloader;
    }

    /**
     * Retrieves session object.
     */
    public function session(): Session
    {
        return $this->session;
    }

    /**
     * Retrieves cess uri plus given endpoint.
     */
    public function cessUrl(string $endpoint = ''): string
    {
        return $this->config->cessUrl().$endpoint;
    }

    /**
     * Retrieves vaultid uri plus given endpoint.
     */
    public function vaultIdUrl(string $endpoint = ''): string
    {
        return $this->config->vaultIdUrl().$endpoint;
    }

    /**
     * Retrieves birdid uri plus given endpoint.
     */
    public function birdIdUrl(string $endpoint = ''): string
    {
        return $this->config->birdIdUrl().$endpoint;
    }
}
