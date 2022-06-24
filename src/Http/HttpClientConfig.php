<?php

declare(strict_types=1);

namespace ArangoClient\Http;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * Class HttpClientConfig
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * (Guzzle uses snake_case for its configuration options)
 */
class HttpClientConfig extends DataTransferObject
{
    public string $endpoint = 'http://localhost:8529';

    public ?string $host = null;

    public string|int|null $port = null;

    public int|float $version = 1.1;

    public string $connection = 'Keep-Alive';

    /**
     * @var array<mixed>|false
     */
    public $allow_redirects = false;

    public float $connect_timeout = 0;

    public ?string $username = null;

    public ?string $password = null;

    public string $database = '_system';

    /**
     * @return array<array<mixed>|string|numeric|bool|null>
     */
    public function mapGuzzleHttpClientConfig(): array
    {
        $config = [];
        $config['base_uri'] = $this->endpoint;
        $config['version'] = $this->version;
        $config['allow_redirects'] = $this->allow_redirects;
        $config['connect_timeout'] = $this->connect_timeout;

        $config['auth'] = [
            $this->username,
            $this->password,
        ];
        $config['headers'] = [
            'Connection' => $this->connection,
        ];

        return $config;
    }
}
