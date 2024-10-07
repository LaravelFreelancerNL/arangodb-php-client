<?php

uses(Tests\TestCase::class);

declare(strict_types=1);
beforeEach(function () {
});


test('get database', function () {
    $this->arangoClient->setDatabase('_system');
    $result = $this->schemaManager->getCurrentDatabase();

    expect($result->id)->toBe('1');
    expect($result->name)->toBe('_system');
    expect($result->isSystem)->toBe(true);
    expect($result->path)->toBe('none');
});

test('get databases', function () {
    $result = $this->schemaManager->getDatabases();

    expect(2)->toBeLessThanOrEqual(count($result));
    foreach ($result as $database) {
        expect($database)->toBeString();
    }
});

test('create and delete database', function () {
    $database = 'arangodb_php_client_database__test';
    $existingDatabases = $this->schemaManager->getDatabases();

    if (!in_array($database, $existingDatabases)) {
        $result = $this->schemaManager->createDatabase($database);
        expect($result)->toBeTrue();
    }

    $result = $this->schemaManager->deleteDatabase($database);
    expect($result)->toBeTrue();
    $existingDatabases = $this->schemaManager->getDatabases();
    $this->assertNotContains($database, $existingDatabases);
});

test('has database', function () {
    $check = $this->schemaManager->hasDatabase('someNoneExistingDatabase');
    expect($check)->toBeFalse();

    $check = $this->schemaManager->hasDatabase($this->testDatabaseName);
    expect($check)->toBeTrue();
});
