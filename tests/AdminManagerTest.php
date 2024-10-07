<?php

declare(strict_types=1);

use ArangoClient\Admin\AdminManager;

uses(Tests\TestCase::class);
beforeEach(function () {
    $this->adminManager = new AdminManager($this->arangoClient);
});


test('get version', function () {
    $result = $this->adminManager->getVersion();

    expect($result->server)->toBe('arango');
    expect($result->license)->toBe('community');
    expect($result->version)->toBeString();
});

test('get version with details', function () {
    $result = $this->adminManager->getVersion(true);

    expect($result->server)->toBe('arango');
    expect($result->license)->toBe('community');
    expect($result->version)->toBeString();
});

test('get running transactions', function () {
    $transactions = $this->adminManager->getRunningTransactions();
    expect($transactions)->toBeEmpty();
});
