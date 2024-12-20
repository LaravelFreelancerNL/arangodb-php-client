<?php

declare(strict_types=1);

use ArangoClient\Admin\AdminManager;
use ArangoClient\ArangoClient;
use ArangoClient\Http\HttpClientConfig;
use ArangoClient\Schema\SchemaManager;
use ArangoClient\Statement\Statement;
use GuzzleHttp\Client;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

use function PHPUnit\Framework\assertTrue;

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
    expect($config)->toBe($defaultConfig);
});

test('get config with endpoint without host port', function () {
    $config = [
        'endpoint' => 'http://localhost:8529',
        'username' => 'root',
    ];

    $returnedConfig = $this->arangoClient->getConfig();
    expect($returnedConfig['endpoint'])->toBe($config['endpoint']);
});

test('client with host port config', function () {
    $config = [
        'host' => 'http://127.0.0.1',
        'port' => '8529',
        'username' => 'root',
    ];
    $client = new ArangoClient($config);
    $retrievedConfig = $client->getConfig();

    expect($retrievedConfig['endpoint'])->toEqual('http://127.0.0.1:8529');
});

test('config with alien properties', function () {
    $config = [
        'name' => 'arangodb',
        'driver' => 'arangodb',
        'host' => 'http://127.0.0.1',
        'port' => '8529',
        'username' => 'root',
    ];
    $client = new ArangoClient($config);
    $retrievedConfig = $client->getConfig();

    $this->assertArrayNotHasKey('name', $retrievedConfig);
    $this->assertArrayNotHasKey('driver', $retrievedConfig);
});

test('set and get http client', function () {
    $oldClient = $this->arangoClient->getHttpClient();

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

    $config = new HttpClientConfig($defaultConfig);

    $newClient = new GuzzleClient($config->mapGuzzleHttpClientConfig());

    $this->arangoClient->setHttpClient($newClient);

    $retrievedClient = $this->arangoClient->getHttpClient();

    expect($oldClient)->toBeInstanceOf(Client::class);
    expect($retrievedClient::class)->toEqual($newClient::class);
});

test('request', function () {
    $result = $this->arangoClient->request('get', '/_api/version', []);

    expect($result->server)->toBe('arango');
    expect($result->license)->toBe('community');
    expect($result->version)->toBeString();
});

test('get user', function () {
    $user = $this->arangoClient->getUser();
    expect($user)->toBe('root');
});

test('set and get database name', function () {
    $database = $this->arangoClient->getDatabase();
    expect($database)->toBe($this->testDatabaseName);

    $newDatabaseName = 'ArangoClientDB';
    $this->arangoClient->setDatabase($newDatabaseName);

    $database = $this->arangoClient->getDatabase();
    expect($database)->toBe($newDatabaseName);

    // Reset DB name
    $this->arangoClient->setDatabase($this->testDatabaseName);
});

test('database name is used in requests', function () {
    $database = 'arangodb_php_client__test';
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
        expect($transaction['request']->getUri()->getPath())->toBe('/_db/' . $database . $uri);
    }

    $this->arangoClient->schema()->deleteDatabase($database);
});

test('schema', function () {
    $result = $this->arangoClient->schema();
    expect($result)->toBeInstanceOf(SchemaManager::class);

    $database = $this->arangoClient->schema()->getCurrentDatabase();

    $this->assertObjectHasProperty('name', $database);
});

test('admin', function () {
    $result = $this->arangoClient->admin();
    expect($result)->toBeInstanceOf(AdminManager::class);

    $version = $this->arangoClient->admin()->getVersion();

    $this->assertObjectHasProperty('version', $version);
});

test('prepare', function () {
    $statement = $this->arangoClient->prepare('FOR doc IN users RETURN doc');

    expect($statement)->toBeInstanceOf(Statement::class);
});

test('connection protocol version', function () {
    checkHttp2Support();

    $uri = '/_api/version';

    $options = [];
    $options['version'] = 2;
    $response = $this->arangoClient->debugRequest('get', $uri, $options);

    expect($response->getProtocolVersion())->toEqual(2);
});

test('connection protocol version with default setting', function () {
    checkHttp2Support();

    $uri = '/_api/version';

    $client = new ArangoClient(['username' => 'root', 'version' => 2.0]);

    $options = [];
    $options['version'] = 2;
    $response = $this->arangoClient->debugRequest('get', $uri, $options);

    expect($response->getProtocolVersion())->toEqual(2);
});

test('json encode', function () {
    $results = $this->arangoClient->jsonEncode([]);

    expect($results)->toBe('{}');
});

test('json encode empty array', function () {
    $results = $this->arangoClient->jsonEncode([]);

    expect($results)->toBe('{}');
});

test('json encode empty string', function () {
    $results = $this->arangoClient->jsonEncode('');

    expect($results)->toBe('""');
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

    expect($users[0])->toEqual($insertResult[0]);

    $this->schemaManager->deleteCollection($collection);
});

test('disconnect', function () {
    $disconnected = $this->arangoClient->disconnect();

    assertTrue($disconnected);
});
