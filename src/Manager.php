<?php

declare(strict_types=1);

namespace ArangoClient;

abstract class Manager
{
    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    protected function sanitizeRequestMetadata(array $data): array
    {
        unset($data['error']);
        unset($data['code']);

        return $data;
    }
}
