<?php

declare(strict_types=1);

use ArangoClient\Admin\AdminManager;

uses(Tests\TestCase::class);
beforeEach(function () {
    $this->adminManager = new AdminManager($this->arangoClient);
});


test('get version', function () {
    $result = $this->adminManager->getVersion();

    $this->assertSame('arango', $result->server);
    $this->assertSame('community', $result->license);
    $this->assertIsString($result->version);
});

test('get version with details', function () {
    $result = $this->adminManager->getVersion(true);

    $this->assertSame('arango', $result->server);
    $this->assertSame('community', $result->license);
    $this->assertIsString($result->version);
});

test('get running transactions', function () {
    $transactions = $this->adminManager->getRunningTransactions();
    $this->assertEmpty($transactions);
});
