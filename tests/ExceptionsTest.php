<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Schema\SchemaClient;

class ExceptionsTest extends TestCase
{
    /**
     * @throws \ArangoClient\Exceptions\ArangoException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function test409ConflictException()
    {
        $database = 'test_arangodb_php_existing_database';
        if (! $this->schemaClient->hasDatabase($database)) {
            $this->schemaClient->createDatabase($database);
        }

       $this->expectExceptionCode(409);
        $this->schemaClient->createDatabase($database);

        $this->schemaClient->deleteDatabase($database);
    }
}