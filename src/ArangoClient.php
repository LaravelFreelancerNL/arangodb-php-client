<?php

declare(strict_types=1);

namespace ArangoClient;

use ArangoClient\Admin\AdminManager;
use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Schema\SchemaManager;
use ArangoClient\Statement\Statement;
use ArangoClient\Transactions\SupportsTransactions;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\StreamWrapper;
use JsonMachine\JsonMachine;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Traversable;

/**
 * The arangoClient handles connections to ArangoDB's HTTP REST API.
 *
 * @see https://www.arangodb.com/docs/stable/http/
 */
class ArangoClient
{
    use SupportsTransactions;

    protected Client $httpClient;

    /**
     * @var string
     */
    protected string $endpoint;

    /**
     * @var array<mixed>|false
     */
    protected $allowRedirects;

    /**
     * @var float
     */
    protected float $connectTimeout;

    /**
     * @var string
     */
    protected string $connection;

    /**
     * @var string|null
     */
    protected ?string $username = null;

    /**
     * @var string|null
     */
    protected ?string $password = null;

    /**
     * @var string
     */
    protected string $database;

    /**
     * @var AdminManager|null
     */
    protected ?AdminManager $adminManager = null;

    /**
     * @var SchemaManager|null
     */
    protected ?SchemaManager $schemaManager = null;

    /**
     * ArangoClient constructor.
     *
     * @param  array<string|numeric|null>  $config
     * @param  Client|null  $httpClient
     */
    public function __construct(array $config = [], Client $httpClient = null)
    {
        $this->endpoint = $this->generateEndpoint($config);
        $this->username = (isset($config['username'])) ? (string) $config['username'] : null;
        $this->password = (isset($config['password'])) ? (string) $config['password'] : null;
        $this->database = (isset($config['database'])) ? (string) $config['database'] : '_system';
        $this->connection = (isset($config['connection'])) ? (string) $config['connection'] : 'Keep-Alive';
        $this->allowRedirects = (isset($config['allow_redirects'])) ? (array) $config['allow_redirects'] : false;
        $this->connectTimeout = (isset($config['connect_timeout'])) ? (float) $config['connect_timeout'] : 0;

        $this->httpClient = isset($httpClient) ? $httpClient : new Client($this->mapHttpClientConfig());
    }

    /**
     * @param  array<mixed>  $config
     * @return string
     */
    public function generateEndpoint(array $config): string
    {
        if (isset($config['endpoint'])) {
            return (string) $config['endpoint'];
        }

        $endpoint = (isset($config['host'])) ? (string) $config['host'] : 'http://localhost';
        $endpoint .= (isset($config['port'])) ? ':' . (string) $config['port'] : ':8529';

        return $endpoint;
    }

    /**
     * @return array<array<mixed>|string|numeric|bool|null>
     */
    protected function mapHttpClientConfig(): array
    {
        $config = [];
        $config['base_uri'] = $this->endpoint;
        $config['allow_redirects'] = $this->allowRedirects;
        $config['connect_timeout'] = $this->connectTimeout;
        $config['auth'] = [
            $this->username,
            $this->password,
        ];
        $config['header'] = [
            'Connection' => $this->connection
        ];
        return $config;
    }

    /**
     * @psalm-suppress MixedReturnStatement
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array<mixed>  $options
     * @param  string|null  $database
     * @return array<mixed>
     * @throws ArangoException
     */
    public function request(string $method, string $uri, array $options = [], ?string $database = null): array
    {
        $uri = $this->prependDatabaseToUri($uri, $database);

        $response = null;
        try {
            $response = $this->httpClient->request($method, $uri, $options);
        } catch (\Throwable $e) {
            $this->handleGuzzleException($e);
        }

        return $this->decodeResponse($response);
    }

    /**
     * @return array<array<mixed>|string|numeric|bool|null>
     */
    public function getConfig(): array
    {
        $config = [];
        $config['endpoint'] = $this->endpoint;
        $config['username'] = $this->username;
        $config['password'] = $this->password;
        $config['database'] = $this->database;
        $config['connection'] = $this->connection;
        $config['allow_redirects'] = $this->allowRedirects;
        $config['connect_timeout'] = $this->connectTimeout;

        return $config;
    }

    /**
     * @psalm-suppress MixedAssignment, MixedArrayOffset
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @param  ResponseInterface|null  $response
     * @return array<mixed>
     */
    protected function decodeResponse(?ResponseInterface $response): array
    {
        if (! isset($response)) {
            return [];
        }

        $decodedResponse = [];

        $phpStream = StreamWrapper::getResource($response->getBody());
        $decodedStream = JsonMachine::fromStream($phpStream);

        foreach ($decodedStream as $key => $value) {
            $decodedResponse[$key] = $value;
        }

        return $decodedResponse;
    }

    /**
     * @param  array<mixed>  $data
     * @return string
     * @throws ArangoException
     */
    public function jsonEncode(array $data): string
    {
        $response = '';

        if (! empty($data)) {
            $response = json_encode($data);
        }
        if (empty($data)) {
            $response = json_encode($data, JSON_FORCE_OBJECT);
        }

        if ($response === false) {
            throw new ArangoException('JSON encoding failed with error: ' . json_last_error_msg(), json_last_error());
        }
        return $response;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return (string) $this->username;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setDatabase(string $name): void
    {
        $this->database = $name;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @param  string  $query
     * @param  array<scalar>  $bindVars
     * @param  array<mixed>  $options
     * @return Traversable<mixed>
     */
    public function prepare(
        string $query,
        array $bindVars = [],
        array $options = []
    ): Traversable {
        return new Statement($this, $query, $bindVars, $options);
    }

    /**
     * @return AdminManager
     */
    public function admin(): AdminManager
    {
        if (! isset($this->adminManager)) {
            $this->adminManager = new AdminManager($this);
        }
        return $this->adminManager;
    }

    public function schema(): SchemaManager
    {
        if (! isset($this->schemaManager)) {
            $this->schemaManager = new SchemaManager($this);
        }
        return $this->schemaManager;
    }

    /**
     * @param  Throwable  $e
     * @throws ArangoException
     */
    protected function handleGuzzleException(Throwable $e): void
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        if ($e instanceof RequestException && $e->hasResponse()) {
            $decodedResponse = $this->decodeResponse($e->getResponse());
            $message = (string) $decodedResponse['errorMessage'];
            $code = (int) $decodedResponse['code'];
        }

        throw(
            new ArangoException(
                $message,
                (int) $code
            )
        );
    }

    protected function prependDatabaseToUri(string $uri, ?string $database = null): string
    {
        if (! isset($database)) {
            $database = $this->database;
        }
        return '/_db/' . urlencode($database) . $uri;
    }

    public function setHttpClient(Client $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }
}
