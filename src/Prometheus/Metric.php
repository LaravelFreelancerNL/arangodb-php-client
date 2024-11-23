<?php

declare(strict_types=1);

namespace ArangoClient\Prometheus;

class Metric
{
    /**
     * @param string $name
     * @param null|string $type
     * @param string|null $help
     * @param int|float|null $count
     * @param int|float|null $sum
     * @param int|float|null $value
     * @param array<array-key, Bucket>|null $buckets
     * @param array<string, float|int|string>|null $labels
     */
    public function __construct(
        public string $name,
        public null|string $type = null,
        public null|string $help = null,
        public int|float|null $count = null,
        public int|float|null $sum = null,
        public int|float|null $value = null,
        public ?array $buckets = [],
        public ?array $labels = [],
        public null|int $timestamp = null,
    ) {}
}
