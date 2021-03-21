<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Connector;
use ArangoClient\DatabaseClient;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

abstract class TestCase extends PhpUnitTestCase
{
    protected Connector $connector;

    protected DatabaseClient $databaseClient;

    protected string $testDatabaseName = 'arangodb_php_client_database__test';

    protected function setUp(): void
    {
        $this->connector = new Connector();

        $this->databaseClient = new DatabaseClient($this->connector);

        $this->createTestDatabase();
    }

    /**
     * @throws \ArangoClient\Exceptions\ArangoDbException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function createTestDatabase()
    {
        if(! $this->databaseClient->exists($this->testDatabaseName)) {
            $this->databaseClient->create($this->testDatabaseName);
        }
    }
}
