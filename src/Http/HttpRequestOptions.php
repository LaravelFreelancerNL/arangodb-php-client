<?php

declare(strict_types=1);

namespace ArangoClient\Http;

use GuzzleHttp\HandlerStack;
use Spatie\DataTransferObject\FlexibleDataTransferObject;

/**
 * Class HttpRequestOptions
 *
 * @package ArangoClient\Http
 */
class HttpRequestOptions extends FlexibleDataTransferObject
{
    /**
     * @var array<mixed>|string|null
     */
    public $query;

    /**
     * @var array<mixed>|null
     */
    public ?array $headers = null;

    public ?string $body = null;

    public ?HandlerStack $handler = null;

    /**
     * @param  string  $key
     * @param mixed $value
     */
    public function addHeader(string $key, $value): void
    {
        $this->headers[$key] = $value;
    }

    /**
     * @return array<mixed>
     */
    public function all(): array
    {
        $array = parent::all();

        return array_filter($array);
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        return array_filter($array);
    }
}
