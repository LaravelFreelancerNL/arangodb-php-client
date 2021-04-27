<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Admin\AdminManager;

class AdminManagerTest extends TestCase
{
    protected AdminManager $adminManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminManager = new AdminManager($this->arangoClient);
    }

    public function testGetVersion()
    {
        $result = $this->adminManager->getVersion();

        $this->assertSame('arango', $result->server);
        $this->assertSame('community', $result->license);
        $this->assertIsString($result->version);

    }

    public function testGetVersionWithDetails()
    {
        $result = $this->adminManager->getVersion(true);

        $this->assertSame('arango', $result->server);
        $this->assertSame('community', $result->license);
        $this->assertIsString($result->version);
    }

    public function testGetRunningTransactions()
    {
        $transactions = $this->adminManager->getRunningTransactions();
        $this->assertEmpty($transactions);
    }

}