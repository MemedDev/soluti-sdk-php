<?php

declare(strict_types=1);

namespace Memed\Soluti;

use Memed\Soluti\TestCase;

class ConfigTest extends TestCase
{
    public function testCessUrlShouldRetrieveStringProperly()
    {
        $config = new Config(['url_cess' => 'some-cess-url']);

        $this->assertEquals('some-cess-url', $config->cessUrl());
    }

    public function testVaultIdUrlShouldRetrieveStringProperly()
    {
        $config = new Config(['url_vaultid' => 'some-vaultid-url']);

        $this->assertEquals('some-vaultid-url', $config->vaultIdUrl());
    }
}
