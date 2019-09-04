<?php

declare(strict_types=1);

namespace Memed\Soluti;

use Memed\Soluti\Http\Client;
use Memed\Soluti\TestCase;
use Mockery as m;

class ManagerTest extends TestCase
{
    public function testCessUrlShouldRetrieveUrlFromConfig()
    {
        $config = m::mock(Config::class);

        $config->shouldReceive('cessUrl')
            ->once()
            ->andReturn('http://cess.url/');

        $manager = new Manager($config, m::mock(Client::class));

        $this->assertEquals('http://cess.url/', $manager->cessUrl());
    }

    public function testCessUrlShouldRetrieveUrlFromConfigConcatenatingEndpoint()
    {
        $config = m::mock(Config::class);

        $config->shouldReceive('cessUrl')
            ->once()
            ->andReturn('http://cess.url/');

        $manager = new Manager($config, m::mock(Client::class));

        $this->assertEquals(
            'http://cess.url/end/point',
            $manager->cessUrl('end/point')
        );
    }

    public function testVaultIdUrlShouldRetrieveUrlFromConfig()
    {
        $config = m::mock(Config::class);

        $config->shouldReceive('vaultIdUrl')
            ->once()
            ->andReturn('http://vaultid.url/');

        $manager = new Manager($config, m::mock(Client::class));

        $this->assertEquals('http://vaultid.url/', $manager->vaultIdUrl());
    }

    public function testVaultIdUrlShouldRetrieveUrlFromConfigConcatenatingEndpoint()
    {
        $config = m::mock(Config::class);

        $config->shouldReceive('vaultIdUrl')
            ->once()
            ->andReturn('http://vaultid.url/');

        $manager = new Manager($config, m::mock(Client::class));

        $this->assertEquals(
            'http://vaultid.url/end/point',
            $manager->vaultIdUrl('end/point')
        );
    }
}
