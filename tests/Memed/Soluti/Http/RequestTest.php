<?php

declare(strict_types=1);

namespace Memed\Soluti\Http;

use PHPUnit\Framework\TestCase as TestCase;

class RequestTest extends TestCase
{
    public function testRequestConstructorShouldUseDefaultHeaders()
    {
        $request = new Request('method', 'some-uri');

        $expected = [
            'Accept' => ['application/json'],
            'Cache-Control' => ['no-cache'],
        ];

        $this->assertEquals($expected, $request->getHeaders());
    }

    public function testRequestConstructorShouldMergeHeaders()
    {
        $headers = [
            'Additional' => 'Header',
        ];

        $request = new Request('method', 'some-uri', [], $headers);

        $expected = [
            'Accept' => ['application/json'],
            'Cache-Control' => ['no-cache'],
            'Additional' => ['Header'],
        ];

        $this->assertEquals($expected, $request->getHeaders());
    }
}
