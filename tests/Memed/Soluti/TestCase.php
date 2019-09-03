<?php

declare(strict_types=1);

namespace Memed\Soluti;

use Mockery as m;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * {@inheritdoc}
     */
    public function teardown(): void
    {
        parent::teardown();
        m::close();
    }
}
