<?php

uses(Tests\TestCase::class);

declare(strict_types=1);

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
    $this->expectExceptionCode(404);
    $this->schemaManager->hasCollection('dummy');
});
