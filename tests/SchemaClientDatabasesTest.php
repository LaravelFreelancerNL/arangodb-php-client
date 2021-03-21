<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Schema\SchemaClient;
use GuzzleHttp\Exception\GuzzleException;


class SchemaClientDatabasesTest extends TestCase
{
    protected SchemaClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new SchemaClient($this->connector);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetDatabase()
    {
        $result = $this->client->getCurrentDatabase();

        $this->assertSame('1', $result['id']);
        $this->assertSame('_system', $result['name']);
        $this->assertSame(true, $result['isSystem']);
        $this->assertSame('none', $result['path']);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testListDatabases()
    {
        $result = $this->client->listDatabases();

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
    public function testListMyDatabases()
    {
        $result = $this->client->listMyDatabases();

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
        $existingDatabases = $this->client->listDatabases();

        if (! in_array($database, $existingDatabases)) {
            $result = $this->client->createDatabase($database);
            $this->assertTrue($result);
        }

        $result = $this->client->deleteDatabase($database);
        $this->assertTrue($result);
        $existingDatabases = $this->client->listDatabases();
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