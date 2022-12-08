<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Admin\AdminManager;
use ArangoClient\ArangoClient;
use ArangoClient\Schema\SchemaManager;
use ArangoClient\Statement\Statement;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Mockery;
use stdClass;

class ArangoClientTest extends TestCase
{
    public function testGetConfig()
    {
        $defaultConfig = [
            'endpoint' => 'http://localhost:8529',
            'host' => null,
            'port' => null,
            'version' => 1.1,
            'connection' => 'Keep-Alive',
            'allow_redirects' => false,
            'connect_timeout' => 0.0,
            'username' => 'root',
            'password' => null,
            'database' => $this->testDatabaseName,
        ];

        $config = $this->arangoClient->getConfig();
        $this->assertSame($defaultConfig, $config);
    }

    public function testGetConfigWithEndpointWithoutHostPort()
    {
        $config = [
            'endpoint' => 'http://localhost:8529',
            'username' => 'root',
        ];

        $returnedConfig = $this->arangoClient->getConfig();
        $this->assertSame($config['endpoint'], $returnedConfig['endpoint']);
    }

    public function testClientWithHostPortConfig()
    {
        $config = [
            'host' => 'http://127.0.0.1',
            'port' => '1234',
            'username' => 'root',
        ];
        $client = new ArangoClient($config);
        $retrievedConfig = $client->getConfig();

        $this->assertEquals('http://127.0.0.1:1234', $retrievedConfig['endpoint']);
    }

    public function testConfigWithAlienProperties()
    {
        $config = [
            'name' => 'arangodb',
            'driver' => 'arangodb',
            'host' => 'http://127.0.0.1',
            'port' => '1234',
            'username' => 'root',
        ];
        $client = new ArangoClient($config);
        $retrievedConfig = $client->getConfig();

        $this->assertArrayNotHasKey('name', $retrievedConfig);
        $this->assertArrayNotHasKey('driver', $retrievedConfig);
    }

    public function testSetAndGetHttpClient()
    {
        $oldClient = $this->arangoClient->getHttpClient();

        $newClient = Mockery::mock(Client::class);
        $this->arangoClient->setHttpClient($newClient);
        $retrievedClient = $this->arangoClient->getHttpClient();

        $this->assertInstanceOf(Client::class, $oldClient);
        $this->assertEquals($newClient::class, $retrievedClient::class);
    }

    public function testRequest()
    {
        $result = $this->arangoClient->request('get', '/_api/version', []);

        $this->assertSame('arango', $result->server);
        $this->assertSame('community', $result->license);
        $this->assertIsString($result->version);
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
            new Response(200, ['X-Foo' => 'Bar'], '{}'),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $this->arangoClient->setDatabase($database);
        $this->arangoClient->request('get', $uri, ['handler' => $handlerStack]);

        foreach ($container as $transaction) {
            $this->assertSame('/_db/'.$database.$uri, $transaction['request']->getUri()->getPath());
        }

        $this->arangoClient->schema()->deleteDatabase($database);
    }

    public function testSchema()
    {
        $result = $this->arangoClient->schema();
        $this->assertInstanceOf(SchemaManager::class, $result);

        $database = $this->arangoClient->schema()->getCurrentDatabase();

        $this->assertObjectHasAttribute('name', $database);
    }

    public function testAdmin()
    {
        $result = $this->arangoClient->admin();
        $this->assertInstanceOf(AdminManager::class, $result);

        $version = $this->arangoClient->admin()->getVersion();

        $this->assertObjectHasAttribute('version', $version);
    }

    public function testPrepare()
    {
        $statement = $this->arangoClient->prepare('FOR doc IN users RETURN doc');

        $this->assertInstanceOf(Statement::class, $statement);
    }

    public function testConnectionProtocolVersion()
    {
        $this->checkHttp2Support();

        $uri = '/_api/version';

        $options = [];
        $options['version'] = 2;
        $response = $this->arangoClient->debugRequest('get', $uri, $options);

        $this->assertEquals(2, $response->getProtocolVersion());
    }

    public function testConnectionProtocolVersionWithDefaultSetting()
    {
        $this->checkHttp2Support();

        $uri = '/_api/version';

        $client = new ArangoClient(['username' => 'root', 'version' => 2.0]);

        $options = [];
        $options['version'] = 2;
        $response = $this->arangoClient->debugRequest('get', $uri, $options);

        $this->assertEquals(2, $response->getProtocolVersion());
    }

    public function testJsonEncode()
    {
        $results = $this->arangoClient->jsonEncode([]);

        $this->assertSame('{}', $results);
    }

    public function testJsonEncodeEmptyArray()
    {
        $results = $this->arangoClient->jsonEncode([]);

        $this->assertSame('{}', $results);
    }

    public function testJsonEncodeEmptyString()
    {
        $results = $this->arangoClient->jsonEncode('');

        $this->assertSame('""', $results);
    }

    public function testJsonEncodeInvalidData()
    {
        $data = [];
        $data[] = "\xB1\x31";
        $this->expectExceptionCode(JSON_ERROR_UTF8);
        $this->arangoClient->jsonEncode($data);
    }

    public function testResponseDataMatchesRequestData()
    {
        $collection = 'users';
        if (! $this->schemaManager->hasCollection($collection)) {
            $this->schemaManager->createCollection($collection);
        }
        $location = new stdClass();
        $location->address = 'Voughtstreet 10';
        $location->city = 'New York';

        $user = new stdClass();
        $user->name = 'Soldier Boy';
        $user->location = $location;

        $insertQuery = 'INSERT '.json_encode($user, JSON_THROW_ON_ERROR).' INTO '.$collection.' RETURN NEW';
        $insertStatement = $this->arangoClient->prepare($insertQuery);
        $insertStatement->execute();
        $insertResult = $insertStatement->fetchAll();

        $query = 'FOR doc IN '.$collection.' RETURN doc';
        $statement = $this->arangoClient->prepare($query);
        $statement->execute();
        $users = $statement->fetchAll();

        $this->assertEquals($insertResult[0], $users[0]);

        $this->schemaManager->deleteCollection($collection);
    }

    protected function checkHttp2Support()
    {
        // First assert that CURL supports http2!
        if (! curl_version()['features'] || CURL_VERSION_HTTP2 === 0) {
            $this->markTestSkipped('The installed version of CURL does not support the HTTP2 protocol.');
        }
        // HTTP/2 is only supported by ArangoDB 3.7 and up.
        $this->skipTestOnArangoVersions('3.7');
    }
}
