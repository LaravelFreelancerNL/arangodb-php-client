<?php

declare(strict_types=1);

use ArangoClient\Admin\AdminManager;
use ArangoClient\ArangoClient;
use ArangoClient\Schema\SchemaManager;
use ArangoClient\Statement\Statement;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

uses(Tests\TestCase::class);

test('get config', function () {
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
        'jsonStreamDecoderThreshold' => 1048576,
    ];

    $config = $this->arangoClient->getConfig();
    $this->assertSame($defaultConfig, $config);
});

test('get config with endpoint without host port', function () {
    $config = [
        'endpoint' => 'http://localhost:8529',
        'username' => 'root',
    ];

    $returnedConfig = $this->arangoClient->getConfig();
    $this->assertSame($config['endpoint'], $returnedConfig['endpoint']);
});

test('client with host port config', function () {
    $config = [
        'host' => 'http://127.0.0.1',
        'port' => '1234',
        'username' => 'root',
    ];
    $client = new ArangoClient($config);
    $retrievedConfig = $client->getConfig();

    $this->assertEquals('http://127.0.0.1:1234', $retrievedConfig['endpoint']);
});

test('config with alien properties', function () {
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
});

test('set and get http client', function () {
    $oldClient = $this->arangoClient->getHttpClient();

    $newClient = Mockery::mock(Client::class);
    $this->arangoClient->setHttpClient($newClient);
    $retrievedClient = $this->arangoClient->getHttpClient();

    $this->assertInstanceOf(Client::class, $oldClient);
    $this->assertEquals($newClient::class, $retrievedClient::class);
});

test('request', function () {
    $result = $this->arangoClient->request('get', '/_api/version', []);

    $this->assertSame('arango', $result->server);
    $this->assertSame('community', $result->license);
    $this->assertIsString($result->version);
});

test('get user', function () {
    $user = $this->arangoClient->getUser();
    $this->assertSame('root', $user);
});

test('set and get database name', function () {
    $database = $this->arangoClient->getDatabase();
    $this->assertSame($this->testDatabaseName, $database);

    $newDatabaseName = 'ArangoClientDB';
    $this->arangoClient->setDatabase($newDatabaseName);

    $database = $this->arangoClient->getDatabase();
    $this->assertSame($newDatabaseName, $database);
});

test('database name is used in requests', function () {
    $database = 'some_database';
    if (!$this->arangoClient->schema()->hasDatabase($database)) {
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
        $this->assertSame('/_db/' . $database . $uri, $transaction['request']->getUri()->getPath());
    }

    $this->arangoClient->schema()->deleteDatabase($database);
});

test('schema', function () {
    $result = $this->arangoClient->schema();
    $this->assertInstanceOf(SchemaManager::class, $result);

    $database = $this->arangoClient->schema()->getCurrentDatabase();

    $this->assertObjectHasProperty('name', $database);
});

test('admin', function () {
    $result = $this->arangoClient->admin();
    $this->assertInstanceOf(AdminManager::class, $result);

    $version = $this->arangoClient->admin()->getVersion();

    $this->assertObjectHasProperty('version', $version);
});

test('prepare', function () {
    $statement = $this->arangoClient->prepare('FOR doc IN users RETURN doc');

    $this->assertInstanceOf(Statement::class, $statement);
});

test('connection protocol version', function () {
    checkHttp2Support();

    $uri = '/_api/version';

    $options = [];
    $options['version'] = 2;
    $response = $this->arangoClient->debugRequest('get', $uri, $options);

    $this->assertEquals(2, $response->getProtocolVersion());
});

test('connection protocol version with default setting', function () {
    checkHttp2Support();

    $uri = '/_api/version';

    $client = new ArangoClient(['username' => 'root', 'version' => 2.0]);

    $options = [];
    $options['version'] = 2;
    $response = $this->arangoClient->debugRequest('get', $uri, $options);

    $this->assertEquals(2, $response->getProtocolVersion());
});

test('json encode', function () {
    $results = $this->arangoClient->jsonEncode([]);

    $this->assertSame('{}', $results);
});

test('json encode empty array', function () {
    $results = $this->arangoClient->jsonEncode([]);

    $this->assertSame('{}', $results);
});

test('json encode empty string', function () {
    $results = $this->arangoClient->jsonEncode('');

    $this->assertSame('""', $results);
});

test('json encode invalid data', function () {
    $data = [];
    $data[] = "\xB1\x31";
    $this->expectExceptionCode(JSON_ERROR_UTF8);
    $this->arangoClient->jsonEncode($data);
});

test('response data matches request data', function () {
    $collection = 'users';
    if (!$this->schemaManager->hasCollection($collection)) {
        $this->schemaManager->createCollection($collection);
    }
    $location = new stdClass();
    $location->address = 'Voughtstreet 10';
    $location->city = 'New York';

    $user = new stdClass();
    $user->name = 'Soldier Boy';
    $user->location = $location;

    $insertQuery = 'INSERT ' . json_encode($user, JSON_THROW_ON_ERROR) . ' INTO ' . $collection . ' RETURN NEW';
    $insertStatement = $this->arangoClient->prepare($insertQuery);
    $insertStatement->execute();
    $insertResult = $insertStatement->fetchAll();

    $query = 'FOR doc IN ' . $collection . ' RETURN doc';
    $statement = $this->arangoClient->prepare($query);
    $statement->execute();
    $users = $statement->fetchAll();

    $this->assertEquals($insertResult[0], $users[0]);

    $this->schemaManager->deleteCollection($collection);
});

// Helpers
function checkHttp2Support()
{
    // First assert that CURL supports http2!
    if (!curl_version()['features'] || CURL_VERSION_HTTP2 === 0) {
        test()->markTestSkipped('The installed version of CURL does not support the HTTP2 protocol.');
    }
    // HTTP/2 is only supported by ArangoDB 3.7 and up.
    test()->skipTestOnArangoVersions('3.7');
}
