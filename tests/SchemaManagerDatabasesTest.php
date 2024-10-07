<?php

uses(Tests\TestCase::class);

declare(strict_types=1);
beforeEach(function () {
});


test('get database', function () {
    $this->arangoClient->setDatabase('_system');
    $result = $this->schemaManager->getCurrentDatabase();

    $this->assertSame('1', $result->id);
    $this->assertSame('_system', $result->name);
    $this->assertSame(true, $result->isSystem);
    $this->assertSame('none', $result->path);
});

test('get databases', function () {
    $result = $this->schemaManager->getDatabases();

    $this->assertLessThanOrEqual(count($result), 2);
    foreach ($result as $database) {
        $this->assertIsString($database);
    }
});

test('create and delete database', function () {
    $database = 'arangodb_php_client_database__test';
    $existingDatabases = $this->schemaManager->getDatabases();

    if (!in_array($database, $existingDatabases)) {
        $result = $this->schemaManager->createDatabase($database);
        $this->assertTrue($result);
    }

    $result = $this->schemaManager->deleteDatabase($database);
    $this->assertTrue($result);
    $existingDatabases = $this->schemaManager->getDatabases();
    $this->assertNotContains($database, $existingDatabases);
});

test('has database', function () {
    $check = $this->schemaManager->hasDatabase('someNoneExistingDatabase');
    $this->assertFalse($check);

    $check = $this->schemaManager->hasDatabase($this->testDatabaseName);
    $this->assertTrue($check);
});
