<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Admin\AdminManager;
use ArangoClient\Schema\SchemaManager;
use ArangoClient\Statement;

class ArangoClientTest extends TestCase
{
    public function testGetConfig()
    {
        $defaultConfig = [
            'host' => 'http://localhost',
            'port' => '8529',
            'AuthUser' => 'root',
            'AuthPassword' => null,
            'AuthType' => 'basic',
            'base_uri' => 'http://localhost:8529'
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