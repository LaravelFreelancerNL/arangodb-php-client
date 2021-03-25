<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Admin\AdminManager;
use ArangoClient\Schema\SchemaManager;
use ArangoClient\Statement\Statement;

class ArangoClientTest extends TestCase
{
    public function testGetConfig()
    {
        $defaultConfig = [
            'endpoint' => 'http://localhost:8529',
            'username' => 'root',
            'password' => null,
            'database' => '_system',
            'connection' => 'Keep-Alive',
            'allow_redirects' => false,
            'connect_timeout' => 0.0
        ];

        $config = $this->arangoClient->getConfig();
        $this->assertSame($defaultConfig, $config);
    }

    public function testRequest()
    {
        $result = $this->arangoClient->request('get', '/_api/version', []);

        $this->assertSame('arango', $result['server']);
        $this->assertSame('community', $result['license']);
        $this->assertIsString($result['version']);
    }

    public function testGetUser()
    {
        $user = $this->arangoClient->getUser();
        $this->assertSame('root', $user);
    }

    public function testSetAndGetDatabaseName()
    {
        $database = $this->arangoClient->getDatabase();
        $this->assertSame('_system', $database);

        $newDatabaseName = 'ArangoClientDB';
        $this->arangoClient->setDatabase($newDatabaseName);

        $database = $this->arangoClient->getDatabase();
        $this->assertSame($newDatabaseName, $database);
    }

    public function testSchema()
    {
        $result = $this->arangoClient->schema();
        $this->assertInstanceOf(SchemaManager::class, $result);

        $database = $this->arangoClient->schema()->getCurrentDatabase();

        $this->assertArrayHasKey('name', $database);
    }

    public function testAdmin()
    {
        $result = $this->arangoClient->admin();
        $this->assertInstanceOf(AdminManager::class, $result);

        $version = $this->arangoClient->admin()->getVersion();

        $this->assertArrayHasKey('version', $version);
    }

    public function testPrepare()
    {
        $statement = $this->arangoClient->prepare('FOR doc IN users RETURN doc');

        $this->assertInstanceOf(Statement::class, $statement);
    }
}