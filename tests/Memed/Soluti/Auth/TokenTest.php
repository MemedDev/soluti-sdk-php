<?php

declare(strict_types=1);

namespace Memed\Soluti\Auth;

use PHPUnit\Framework\TestCase as TestCase;

class TokenTest extends TestCase
{
    public function testTokenCastingToStringShouldRetrieveToken()
    {
        $token = new Token('some token', 'some-type', 0, 'some-scope');

        $this->assertEquals('Some-type some token', (string) $token);
    }
}
