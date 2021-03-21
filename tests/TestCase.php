<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Connector;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

abstract class TestCase extends PhpUnitTestCase
{
    protected $connector;

    protected function setUp(): void
    {
        $this->connector = new Connector();
    }
}
