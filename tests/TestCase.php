<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Administration\AdministrationClient;
use ArangoClient\Connector;
use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Schema\SchemaClient;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

abstract class TestCase extends PhpUnitTestCase
{
    protected Connector $connector;

    protected SchemaClient $schemaClient;

    protected AdministrationClient $administrationClient;

    protected string $testDatabaseName = 'arangodb_php_client__test';

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    protected function setUp(): void
    {
        $this->connector = new Connector();

        $this->schemaClient = new SchemaClient($this->connector);
        $this->administrationClient = new AdministrationClient($this->connector);

        $this->createTestDatabase();
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    protected function createTestDatabase()
    {
        if(! $this->schemaClient->hasDatabase($this->testDatabaseName)) {
            $this->schemaClient->createDatabase($this->testDatabaseName);
        }
    }
}
