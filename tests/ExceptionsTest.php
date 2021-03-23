<?php

declare(strict_types=1);

namespace Tests;

class ExceptionsTest extends TestCase
{

    public function test409ConflictException()
    {
        $database = 'test_arangodb_php_existing_database';
        if (! $this->schemaManager->hasDatabase($database)) {
            $this->schemaManager->createDatabase($database);
        }

       $this->expectExceptionCode(409);
        $this->schemaManager->createDatabase($database);

        $this->schemaManager->deleteDatabase($database);
    }
}