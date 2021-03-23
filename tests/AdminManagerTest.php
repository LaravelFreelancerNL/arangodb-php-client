<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Admin\AdminManager;

class AdminManagerTest extends TestCase
{
    protected AdminManager $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new AdminManager($this->arangoClient);
    }

    public function testGetVersion()
    {
        $result = $this->client->getVersion();

        $this->assertSame('arango', $result['server']);
        $this->assertSame('community', $result['license']);
        $this->assertIsString($result['version']);

    }

    public function testGetVersionWithDetails()
    {
        $result = $this->client->getVersion(true);

        $this->assertSame('arango', $result['server']);
        $this->assertSame('community', $result['license']);
        $this->assertIsString($result['version']);
        $this->assertArrayHasKey('details', $result);
        $this->assertNotEmpty($result['details']);

    }

}