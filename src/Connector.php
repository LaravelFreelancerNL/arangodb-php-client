<?php

declare(strict_types=1);

namespace ArangoClient;

use ArangoClient\Exceptions\ArangoException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\StreamWrapper;
use JsonMachine\JsonMachine;
use Psr\Http\Message\ResponseInterface;

/*
 * The connector handles connections to ArangoDB's HTTP REST API.
 * @see https://www.arangodb.com/docs/stable/http/
 */
class Connector implements ConnectorInterface
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
     * Connector constructor.
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
     * @return mixed
     * @throws ArangoException|GuzzleException
     * @throws \Exception
     */
    public function request(string $method, string $uri, array $options = [])
    {
        $response = null;
        try {
            $response = $this->httpClient->request($method, $uri, $options);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            if (isset($response)) {
                $decodeResponse = $this->decodeResponse($response);
                throw(
                    new ArangoException(
                        (string) $decodeResponse['errorMessage'],
                        (int) $decodeResponse['code'],
                        $e
                    )
                );
            }
            throw($e);
        }

        $decodeResponse = $this->decodeResponse($response);

        if (isset($decodeResponse['result'])) {
            return $decodeResponse['result'];
        }

        return $decodeResponse;
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
     * @param  ResponseInterface  $response
     * @return array<array-key, mixed>
     */
    protected function decodeResponse(ResponseInterface $response): array
    {
        $decodedResponse = [];

        $phpStream = StreamWrapper::getResource($response->getBody());
        $decodedStream = JsonMachine::fromStream($phpStream);

        foreach ($decodedStream as $key => $value) {
            $decodedResponse[$key] = $value;
        }

        return $decodedResponse;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return (string) $this->config['AuthUser'];
    }
}
