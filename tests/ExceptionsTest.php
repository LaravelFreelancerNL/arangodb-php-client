<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Schema\SchemaClient;

class ExceptionsTest extends TestCase
{
    public function test409ConflictException()
    {
        $database = 'test_arangodb_php_existing_database';
        $databaseClient =  new SchemaClient($this->connector);
        $existingDatabases = $databaseClient->listDatabases();

        if (! in_array($database, $existingDatabases)) {
            $result = $databaseClient->createDatabase($database);
        }
        $this->expectExceptionCode(409);
        $databaseClient->createDatabase($database);

        $databaseClient->deleteDatabase($database);
    }
}