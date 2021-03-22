<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Schema\SchemaClient;
use GuzzleHttp\Exception\GuzzleException;


class SchemaClientDatabasesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetDatabase()
    {
        $result = $this->schemaClient->getCurrentDatabase();

        $this->assertSame('1', $result['id']);
        $this->assertSame('_system', $result['name']);
        $this->assertSame(true, $result['isSystem']);
        $this->assertSame('none', $result['path']);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetDatabases()
    {
        $result = $this->schemaClient->getDatabases();

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(count($result), 2);
        foreach ($result as $database) {
            $this->assertIsString($database);
        }
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testCreateAndDeleteDatabase()
    {
        $database = 'arangodb_php_client_database__test';
        $existingDatabases = $this->schemaClient->getDatabases();

        if (! in_array($database, $existingDatabases)) {
            $result = $this->schemaClient->createDatabase($database);
            $this->assertTrue($result);
        }

        $result = $this->schemaClient->deleteDatabase($database);
        $this->assertTrue($result);
        $existingDatabases = $this->schemaClient->getDatabases();
        $this->assertNotContains($database, $existingDatabases);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testHasDatabase()
    {
        $check = $this->schemaClient->hasDatabase('someNoneExistingDatabase');
        $this->assertFalse($check);

        $check = $this->schemaClient->hasDatabase($this->testDatabaseName);
        $this->assertTrue($check);
    }
}