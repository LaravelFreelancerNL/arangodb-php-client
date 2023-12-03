<?php

declare(strict_types=1);

namespace ArangoClient\Http;

use GuzzleHttp\HandlerStack;
use Spatie\LaravelData\Data;

/**
 * Class HttpRequestOptions
 */
class HttpRequestOptions extends Data
{
    public function __construct(
        /**
         * @var array<mixed>|string|null
         */
        public string|array|null $query = null,
        /**
         * @var array<mixed>|null
         */
        public ?array $headers = null,
        public ?string $body = null,
        public ?HandlerStack $handler = null
    ) {}

    public function addHeader(string $key, mixed $value): void
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
}
