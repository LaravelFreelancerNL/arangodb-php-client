<?php

declare(strict_types=1);

namespace Tests;

class SchemaManagerDatabasesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testGetDatabase()
    {
        $result = $this->schemaManager->getCurrentDatabase();

        $this->assertSame('1', $result['id']);
        $this->assertSame('_system', $result['name']);
        $this->assertSame(true, $result['isSystem']);
        $this->assertSame('none', $result['path']);
    }

    public function testGetDatabases()
    {
        $result = $this->schemaManager->getDatabases();

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(count($result), 2);
        foreach ($result as $database) {
            $this->assertIsString($database);
        }
    }

    public function testCreateAndDeleteDatabase()
    {
        $database = 'arangodb_php_client_database__test';
        $existingDatabases = $this->schemaManager->getDatabases();

        if (! in_array($database, $existingDatabases)) {
            $result = $this->schemaManager->createDatabase($database);
            $this->assertTrue($result);
        }

        $result = $this->schemaManager->deleteDatabase($database);
        $this->assertTrue($result);
        $existingDatabases = $this->schemaManager->getDatabases();
        $this->assertNotContains($database, $existingDatabases);
    }

    public function testHasDatabase()
    {
        $check = $this->schemaManager->hasDatabase('someNoneExistingDatabase');
        $this->assertFalse($check);

        $check = $this->schemaManager->hasDatabase($this->testDatabaseName);
        $this->assertTrue($check);
    }
}