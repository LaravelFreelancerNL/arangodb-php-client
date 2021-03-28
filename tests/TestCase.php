<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Admin\AdminManager;
use ArangoClient\ArangoClient;
use ArangoClient\Schema\SchemaManager;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

abstract class TestCase extends PhpUnitTestCase
{
    protected ArangoClient $arangoClient;

    protected SchemaManager $schemaManager;

    protected AdminManager $administrationClient;

    protected string $testDatabaseName = 'arangodb_php_client__test';

    protected function setUp(): void
    {
        $this->arangoClient = new ArangoClient([
            'username' => 'root'
        ]);

        $this->schemaManager = new SchemaManager($this->arangoClient);
        $this->administrationClient = new AdminManager($this->arangoClient);

        $this->createTestDatabase();
        $this->arangoClient->setDatabase($this->testDatabaseName);
    }

    protected function createTestDatabase()
    {
        $this->arangoClient->setDatabase('_system');
        if(! $this->arangoClient->schema()->hasDatabase($this->testDatabaseName)) {
            $this->arangoClient->schema()->createDatabase($this->testDatabaseName);
        }
    }
}
