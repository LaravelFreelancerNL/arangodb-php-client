<?php

declare(strict_types=1);

namespace ArangoClient;

use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Http\HttpClientConfig;
use ArangoClient\Http\HttpRequestOptions;
use ArangoClient\Statement\Statement;
use ArangoClient\Transactions\SupportsTransactions;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use stdClass;
use Throwable;
use Traversable;

/**
 * The arangoClient handles connections to ArangoDB's HTTP REST API.
 *
 * @see https://www.arangodb.com/docs/stable/http/
 */
class ArangoClient
{
    use HandlesJson;
    use HasManagers;
    use SupportsTransactions;

    protected GuzzleClient $httpClient;

    protected HttpClientConfig $config;

    /**
     * ArangoClient constructor.
     *
     * @param  array<string|numeric|null>  $config
     * @param  GuzzleClient|null  $httpClient
     * @throws UnknownProperties
     */
    public function __construct(array $config = [], GuzzleClient $httpClient = null)
    {
        $config['endpoint'] = $this->generateEndpoint($config);
        $this->config = new HttpClientConfig($config);

        $this->httpClient = $httpClient ?? new GuzzleClient($this->config->mapGuzzleHttpClientConfig());
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
        $endpoint = 'http://localhost:8529';
        if (isset($config['host'])) {
            $endpoint = (string) $config['host'];
        }
        if (isset($config['port'])) {
            $endpoint .= ':' . (string) $config['port'];
        }

        return $endpoint;
    }

    /**
     * @psalm-suppress MixedReturnStatement
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array<mixed>|HttpRequestOptions  $options
     * @param  string|null  $database
     * @return stdClass
     * @throws ArangoException
     */
    public function request(string $method, string $uri, $options = [], ?string $database = null): stdClass
    {
        $uri = $this->prependDatabaseToUri($uri, $database);

        if (is_array($options)) {
            $options = $this->prepareRequestOptions($options);
        }

        $response = null;
        try {
            $response = $this->httpClient->request($method, $uri, $options->all());
        } catch (Throwable $e) {
            $this->handleGuzzleException($e);
        }

        return $this->cleanupResponse($response);
    }

    /**
     * @param  array<mixed>  $options
     * @return HttpRequestOptions
     * @throws ArangoException
     */
    protected function prepareRequestOptions(array $options): HttpRequestOptions
    {
        if (isset($options['body'])) {
            $options['body'] = $this->jsonEncode($options['body']);
        }

        return new HttpRequestOptions($options);
    }

    /**
     * Return the response with debug information (for internal testing purposes).
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array<mixed>  $options
     * @param  string|null  $database
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function debugRequest(
        string $method,
        string $uri,
        array $options = [],
        ?string $database = null
    ): ResponseInterface {
        $uri = $this->prependDatabaseToUri($uri, $database);
        $options['debug'] = true;

        return $this->httpClient->request($method, $uri, $options);
    }

    protected function prependDatabaseToUri(string $uri, ?string $database = null): string
    {
        if (! isset($database)) {
            $database = $this->config->database;
        }
        return '/_db/' . urlencode($database) . $uri;
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
            $message = (string) $decodedResponse->errorMessage;
            $code = (int) $decodedResponse->code;
        }

        throw(
            new ArangoException(
                $code . ' - ' .  $message,
                $code
            )
        );
    }

    /**
     * @psalm-suppress MixedAssignment, MixedArrayOffset
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @param  ResponseInterface|null  $response
     * @return stdClass
     */
    protected function cleanupResponse(?ResponseInterface $response): stdClass
    {
        $response =  $this->decodeResponse($response);
        unset($response->error);
        unset($response->code);

        return $response;
    }

    /**
     * @param  string  $query
     * @param  array<scalar>  $bindVars
     * @param  array<mixed>  $options
     * @return Statement
     */
    public function prepare(
        string $query,
        array $bindVars = [],
        array $options = []
    ): Traversable {
        return new Statement($this, $query, $bindVars, $options);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getConfig(): array
    {
        return $this->config->toArray();
    }

    /**
     * @param string $name
     * @return void
     */
    public function setDatabase(string $name): void
    {
        $this->config->database = $name;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->config->database;
    }

    public function setHttpClient(GuzzleClient $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    public function getHttpClient(): GuzzleClient
    {
        return $this->httpClient;
    }

    public function getUser(): string
    {
        return (string) $this->config->username;
    }
}
