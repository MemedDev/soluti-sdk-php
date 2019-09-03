<?php

declare(strict_types=1);

namespace Memed\Soluti\Transmitter;

use Memed\Soluti\TestCase;

class TokenTest extends TestCase
{
    public function testToStringShouldRetrieveTokenAsString()
    {
        $token = new Token('some-token');

        $this->assertEquals('some-token', (string) $token);
    }
}
