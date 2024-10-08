<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Admin\AdminManager;
use ArangoClient\ArangoClient;
use ArangoClient\Monitor\MonitorManager;
use ArangoClient\Schema\SchemaManager;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

abstract class TestCase extends PhpUnitTestCase
{
    protected ArangoClient $arangoClient;

    protected MonitorManager $monitorManager;

    protected SchemaManager $schemaManager;

    protected AdminManager $administrationClient;

    protected string $testDatabaseName = 'arangodb_php_client__test';

    protected string $accessDatabase = 'arangodb_php_client_access__test';

    protected array $analyzer = [
        'name' => 'testAnalyzerBasics',
        'type' => 'identity',
    ];

    protected string $collection = 'users';

    protected \Traversable $statement;

    protected string $userName = 'kimiko';

    protected array $view = [
        'name' => 'testViewBasics',
        'type' => 'arangosearch',
    ];

    protected function setUp(): void
    {
        $this->arangoClient = new ArangoClient([
            'username' => 'root',
        ]);

        $this->monitorManager = new MonitorManager($this->arangoClient);
        $this->schemaManager = new SchemaManager($this->arangoClient);
        $this->administrationClient = new AdminManager($this->arangoClient);

        $this->createTestDatabase();
        $this->arangoClient->setDatabase($this->testDatabaseName);
    }

    protected function createTestDatabase()
    {
        $this->arangoClient->setDatabase('_system');
        if (!$this->arangoClient->schema()->hasDatabase($this->testDatabaseName)) {
            $this->arangoClient->schema()->createDatabase($this->testDatabaseName);
        }
    }

    public function setUpAccessTest()
    {
        if (!$this->schemaManager->hasDatabase($this->accessDatabase)) {
            $this->schemaManager->createDatabase($this->accessDatabase);
        }
    }

    public function tearDownAccessTest()
    {
        $this->schemaManager->deleteDatabase($this->accessDatabase);
    }

    public function generateTestDocuments(): void
    {
        $query = 'FOR i IN 1..10
      INSERT {
            _key: CONCAT("test", i),
        name: "test",
        foobar: true
      } INTO ' . $this->collection . ' OPTIONS { ignoreErrors: true }';

        $statement = $this->arangoClient->prepare($query);

        $statement->execute();
    }

    public function skipTestOnArangoVersions(string $version, string $operator = '<')
    {
        if (version_compare(getenv('ARANGODB_VERSION'), $version, $operator)) {
            $this->markTestSkipped('This test does not support ArangoDB versions before ' . $version);
        }
    }
}
