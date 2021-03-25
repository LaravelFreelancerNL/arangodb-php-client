<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Admin\AdminManager;
use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Schema\SchemaManager;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

abstract class TestCase extends PhpUnitTestCase
{
    protected ArangoClient $arangoClient;

    protected SchemaManager $schemaManager;

    protected AdminManager $administrationClient;

    protected string $testDatabaseName = 'arangodb_php_client__test';

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    protected function setUp(): void
    {
        $this->arangoClient = new ArangoClient([
            'username' => 'root'
        ]);

        $this->schemaManager = new SchemaManager($this->arangoClient);
        $this->administrationClient = new AdminManager($this->arangoClient);

        $this->createTestDatabase();
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    protected function createTestDatabase()
    {
        if(! $this->schemaManager->hasDatabase($this->testDatabaseName)) {
            $this->schemaManager->createDatabase($this->testDatabaseName);
        }
    }
}
