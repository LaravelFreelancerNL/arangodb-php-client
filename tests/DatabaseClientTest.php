<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\DatabaseClient;


class DatabaseClientTest extends TestCase
{
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new DatabaseClient($this->connector);
    }

    public function testReadDatabase()
    {
        $result = $this->client->read();

        $this->assertSame('1', $result['id']);
        $this->assertSame('_system', $result['name']);
        $this->assertSame(true, $result['isSystem']);
        $this->assertSame('none', $result['path']);
    }

    public function testListDatabases()
    {
        $result = $this->client->listDatabases();

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(count($result), 2);
        foreach ($result as $database) {
            $this->assertIsString($database);
        }
    }

    public function testListMyDatabases()
    {
        $result = $this->client->listMyDatabases();

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(count($result), 2);
        foreach ($result as $database) {
            $this->assertIsString($database);
        }
    }

    public function testCreateAndDeleteDatabase()
    {
        $database = 'test__arangodb_php_client_database';
        $existingDatabases = $this->client->listDatabases();

        if (! in_array($database, $existingDatabases)) {
            $result = $this->client->create($database);
            $this->assertTrue($result);
        }

        $result = $this->client->delete($database);
        $this->assertTrue($result);
        $existingDatabases = $this->client->listDatabases();
        $this->assertNotContains($database, $existingDatabases);

    }
}