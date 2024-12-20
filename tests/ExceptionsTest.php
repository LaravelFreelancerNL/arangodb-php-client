<?php

declare(strict_types=1);

use ArangoClient\Exceptions\ArangoException;

uses(Tests\TestCase::class);

test('test409 conflict exception', function () {
    $database = 'test_arangodb_php_existing_database';
    if (!$this->schemaManager->hasDatabase($database)) {
        $this->schemaManager->createDatabase($database);
    }

    $this->expectExceptionCode(409);
    $this->schemaManager->createDatabase($database);

    $this->schemaManager->deleteDatabase($database);
});

test('calls to none existing db throw', function () {
    $this->arangoClient->setDatabase('NoneExistingDb');
    $this->schemaManager->hasCollection('dummy');

    $this->arangoClient->setDatabase($this->testDatabaseName);
})->throws(ArangoException::class);
