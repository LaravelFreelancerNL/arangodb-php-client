<?php

declare(strict_types=1);

namespace ArangoClient;

use ArangoClient\Admin\AdminManager;
use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Schema\SchemaManager;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\StreamWrapper;
use JsonMachine\JsonMachine;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/*
 * The arangoClient handles connections to ArangoDB's HTTP REST API.
 * @see https://www.arangodb.com/docs/stable/http/
 */
class ArangoClient
{
    /**
     * @var array<string|numeric|null>
     */
    protected array $config = [
        'host' => 'http://localhost',
        'port' => '8529',
        'AuthUser' => 'root',
        'AuthPassword' => null,
        'AuthType' => 'basic'
    ];

    protected Client $httpClient;

    /**
     * @var SchemaManager|null
     */
    protected ?SchemaManager $schemaManager = null;

    /**
     * @var AdminManager|null
     */
    protected ?AdminManager $adminManager = null;

    /**
     * ArangoClient constructor.
     *
     * @param  array<string|numeric|null>|null  $config
     * @param  Client|null  $httpClient
     */
    public function __construct(array $config = null, Client $httpClient = null)
    {
        if ($config !== null) {
            $this->config = $config;
        }

        $this->config = $this->mapHttpClientConfig();

        $this->httpClient = isset($httpClient) ? $httpClient : new Client($this->config);
    }

    /**
     * @psalm-suppress MixedReturnStatement
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array<mixed>  $options
     * @return array<mixed>
     * @throws ArangoException
     */
    public function request(string $method, string $uri, array $options = []): array
    {
        $response = null;
        try {
            $response = $this->httpClient->request($method, $uri, $options);
        } catch (\Throwable $e) {
            $this->handleGuzzleException($e);
        }

        return $this->decodeResponse($response);
    }

    /**
     * @return array<string|numeric|null>
     */
    protected function mapHttpClientConfig(): array
    {
        $this->config['base_uri'] = (string) $this->config['host'] . ':' . (string) $this->config['port'];

        return $this->config;
    }

    /**
     * @return array<string|numeric|null>
     */
    public function getConfig(): array
    {
        return $this->config;
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
     */
    public function jsonEncode(array $data)
    {
        return (string) json_encode($data, JSON_FORCE_OBJECT);
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return (string) $this->config['AuthUser'];
    }

    /**
     * @param  string  $query
     * @param  array<scalar>  $bindVars
     * @param  array<array>  $collections
     * @param  array<scalar>  $options
     * @return Statement
     */
    public function prepare(
        string $query,
        array $bindVars = [],
        array $collections = [],
        array $options = []
    ): Statement {
        return new Statement($this, $query, $bindVars, $collections, $options);
    }

    /**
     * @return SchemaManager
     */
    public function schema(): SchemaManager
    {
        if (! isset($this->schemaManager)) {
            $this->schemaManager = new SchemaManager($this);
        }
        return $this->schemaManager;
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

    /**
     * @param  Throwable  $e
     * @throws ArangoException
     */
    private function handleGuzzleException(Throwable $e): void
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
}
