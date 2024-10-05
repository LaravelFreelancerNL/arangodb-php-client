<?php

declare(strict_types=1);

namespace ArangoClient\Prometheus;

class Bucket
{
    /**
     * @param int|float|null $value
     * @param array<string, mixed>|null $labels
     */
    public function __construct(
        public int|float|null|string $value,
        public array|null $labels,
        public int|float|null|string $timestamp,
    ) {}
}
