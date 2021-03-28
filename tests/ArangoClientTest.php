<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Admin\AdminManager;
use ArangoClient\Schema\SchemaManager;
use ArangoClient\Statement\Statement;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Mockery;

class ArangoClientTest extends TestCase
{
    public function testGetConfig()
    {
        $defaultConfig = [
            'endpoint' => 'http://localhost:8529',
            'username' => 'root',
            'password' => null,
            'database' => $this->testDatabaseName,
            'connection' => 'Keep-Alive',
            'allow_redirects' => false,
            'connect_timeout' => 0.0
        ];

        $config = $this->arangoClient->getConfig();
        $this->assertSame($defaultConfig, $config);
    }


    public function testSetAndGetHttpClient()
    {
        $oldClient = $this->arangoClient->getHttpClient();

        $newClient = Mockery::mock(Client::class);
        $this->arangoClient->setHttpClient($newClient);
        $retrievedClient = $this->arangoClient->getHttpClient();

        $this->assertInstanceOf(Client::class, $oldClient);
        $this->assertEquals(get_class($newClient), get_class($retrievedClient));
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
        $this->assertSame($this->testDatabaseName, $database);

        $newDatabaseName = 'ArangoClientDB';
        $this->arangoClient->setDatabase($newDatabaseName);

        $database = $this->arangoClient->getDatabase();
        $this->assertSame($newDatabaseName, $database);
    }

    public function testDatabaseNameIsUsedInRequests()
    {
        $database = 'some_database';
        if (! $this->arangoClient->schema()->hasDatabase($database)) {
            $this->arangoClient->schema()->createDatabase($database);
        }

        $uri = '/_api/collection';

        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
                                    new Response(200, ['X-Foo' => 'Bar'], '{}')
                                ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $this->arangoClient->setDatabase($database);
        $this->arangoClient->request('get', $uri, ['handler' => $handlerStack]);

        foreach ($container as $transaction) {
            $this->assertSame('/_db/' . $database . $uri, $transaction['request']->getUri()->getPath());
        }

        $this->arangoClient->schema()->deleteDatabase($database);
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