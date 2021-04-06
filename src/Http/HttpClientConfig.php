<?php

declare(strict_types=1);

namespace ArangoClient\Http;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

/**
 * Class HttpClientConfig
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * (Guzzle uses snake_case for its configuration options)
 *
 * @package ArangoClient\Http
 */
class HttpClientConfig extends FlexibleDataTransferObject
{
    /**
     * @var string
     */
    public string $endpoint = 'http://localhost:8529';

    /**
     * @var string|null
     */
    public ?string $host = null;

    /**
     * @var string|int|null
     */
    public $port = null;

    /**
     * @var float|int
     */
    public $version = 1.1;

    /**
     * @var string
     */
    public string $connection = 'Keep-Alive';

    /**
     * @var array<mixed>|false
     */
    public $allow_redirects = false;

    /**
     * @var float
     */
    public float $connect_timeout = 0;

    /**
     * @var string|null
     */
    public ?string $username = null;

    /**
     * @var string|null
     */
    public ?string $password = null;

    /**
     * @var string
     */
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
            'Connection' => $this->connection
        ];
        return $config;
    }
}
