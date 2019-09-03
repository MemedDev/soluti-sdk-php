<?php

declare(strict_types=1);

namespace Memed\Soluti\Http;

use BadMethodCallException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;

/**
 * This class is responsible for abstracting common actions and informations
 * which are used to interact with Soluti's web service.
 */
class Client
{
    protected const REQUEST_TYPE_JSON = 'json';
    protected const REQUEST_TYPE_MULTIPART = 'multipart';

    /**
     * @var GuzzleClient
     */
    protected $guzzle;

    /**
     * Constructor.
     */
    public function __construct(GuzzleClient $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * Sends a request to guzzle using respective content-type and body options
     * for JSON format.
     */
    public function json(Request $request): Response
    {
        return $this->send($request, static::REQUEST_TYPE_JSON);
    }

    /**
     * Sends a request to guzzle using respective content-type and body options
     * for Multipart format.
     */
    public function multipart(Request $request): Response
    {
        return $this->send($request, static::REQUEST_TYPE_MULTIPART);
    }

    /**
     * Sends a request to guzzle using GET method.
     */
    public function get(Request $request, array $options = []): Response
    {
        return $this->guzzle->get(
            $request->getUri(),
            array_merge(
                ['headers' => $request->getHeaders()],
                $options
            )
        );
    }

    /**
     * Sends a request to guzzle and save the response on the destination path
     * given.
     */
    public function download(Request $request, string $destination): Response
    {
        return $this->get($request, ['save_to' => $destination]);
    }

    /**
     * Triggers a request to guzzle.
     */
    private function send(Request $request, string $type): Response
    {
        return $this->guzzle->request(
            $request->getMethod(),
            $request->getUri(),
            [
                'headers' => $request->getHeaders(),
                $type => $request->getData(),
            ]
        );
    }
}
