<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Statement;

class StatementTest extends TestCase
{
    protected Statement $statement;

    protected string $collection = 'users';

    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->schemaManager->hasCollection($this->collection)) {
            $this->schemaManager->createCollection($this->collection);
        }
        $query = 'FOR doc IN ' . $this->collection . ' RETURN doc';

        $this->statement = $this->arangoClient->prepare($query);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->schemaManager->hasCollection($this->collection)) {
            $this->schemaManager->deleteCollection($this->collection);
        }
    }

    public function testSetAndGetQuery()
    {
        $query = 'FOR doc IN ' . $this->collection . ' LIMIT 1 RETURN doc';

        $statement = $this->statement->setQuery($query);

        $this->assertSame($query, $statement->getQuery());
    }

    public function testExecuteSuccess()
    {
        $results = $this->statement->execute();
        $this->assertTrue($results);
    }

    public function testFetchAll()
    {
        $query = 'FOR i IN 1..10
          INSERT {
                _key: CONCAT("test", i),
            name: "test",
            foobar: true
          } INTO ' . $this->collection . ' OPTIONS { ignoreErrors: true }';

        $this->statement->setQuery($query);
        $executed = $this->statement->execute();
        $this->assertTrue($executed);

        $query = 'FOR doc IN ' . $this->collection . ' RETURN doc';
        $this->statement->setQuery($query);
        $executed = $this->statement->execute();
        $this->assertTrue($executed);

        $results = $this->statement->fetchAll();
        $this->assertEquals(10, count($results));
        $this->assertSame('test1', $results[0]['_key']);
    }


    public function testResultsGreaterThanBatchSize()
    {
        // Create 10 objects
        $query = 'FOR i IN 1..10
          INSERT {
                _key: CONCAT("test", i),
            name: "test",
            foobar: true
          } INTO ' . $this->collection . ' OPTIONS { ignoreErrors: true }';
        $statement = $this->arangoClient->prepare($query);
        $statement->execute();

        // Retrieve data in batches of 2
        $query = 'FOR doc IN ' . $this->collection . ' RETURN doc';
        $options = ['batchSize' => 2];
        $statement = $this->arangoClient->prepare($query, [], [], $options);
        $executed = $statement->execute();
        $this->assertTrue($executed);
        $results =$statement->fetchAll();

        $this->assertEquals(10, count($results));
        $this->assertSame('test1', $results[0]['_key']);
    }

//    public function testFetch()
//    {
//        // Create objects
//        $query = 'FOR i IN 1..10
//          INSERT {
//                _key: CONCAT("test", i),
//            name: "test",
//            foobar: true
//          } INTO ' . $this->collection . ' OPTIONS { ignoreErrors: true }';
//        $statement = $this->arangoClient->prepare($query);
//        $statement->execute();
//
//        // Retrieve data
//        $query = 'FOR doc IN ' . $this->collection . ' RETURN doc';
//        $retrievalStatement = $this->arangoClient->prepare($query);
//        $retrievalStatement->execute();
//
//        $results = [];
//        foreach($retrievalStatement->fetch() as $result) {
//            $results[] = $result;
//        }
//        $this->assertEquals(count($results =$retrievalStatement->fetchAll()), count($results));
//    }

    public function testExplain()
    {
        $explanation = $this->statement->explain();

        $this->assertArrayHasKey('plan', $explanation);
    }

    public function testParse()
    {
        $parsed = $this->statement->parse();

        $this->assertArrayHasKey('ast', $parsed);
    }
}