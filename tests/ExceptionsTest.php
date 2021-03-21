<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\DatabaseClient;

class ExceptionsTest extends TestCase
{
    public function test409ConflictException()
    {
        $database = 'test__arangodb_php_existing_database';
        $databaseClient =  new DatabaseClient($this->connector);
        $existingDatabases = $databaseClient->listDatabases();

        if (! in_array($database, $existingDatabases)) {
            $result = $databaseClient->create($database);
            $this->assertTrue($result);
        }
        $this->expectExceptionCode(409);
        $databaseClient->create($database);

        $result = $databaseClient->delete($database);
        $this->assertTrue($result);
    }
}