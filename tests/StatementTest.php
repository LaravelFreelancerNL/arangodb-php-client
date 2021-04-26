<?php

declare(strict_types=1);

namespace Tests;

use Traversable;

class StatementTest extends TestCase
{
    protected Traversable $statement;

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

    protected function generateTestDocuments(): void
    {
        $query = 'FOR i IN 1..10
          INSERT {
                _key: CONCAT("test", i),
            name: "test",
            foobar: true
          } INTO '.$this->collection.' OPTIONS { ignoreErrors: true }';

        $statement = $this->arangoClient->prepare($query);

        $statement->execute();
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

    public function testExplain()
    {
        $explanation = $this->statement->explain();

        $this->assertObjectHasAttribute('plan', $explanation);
    }

    public function testParse()
    {
        $parsed = $this->statement->parse();

        $this->assertObjectHasAttribute('ast', $parsed);
    }

    public function testProfile()
    {
        $profile = $this->statement->profile();
        $this->assertObjectHasAttribute('stats', $profile);
        $this->assertObjectHasAttribute('profile', $profile);
    }

    public function testProfileModeTwo()
    {
        $profile = $this->statement->profile(2);

        $this->assertObjectHasAttribute('stats', $profile);
        $this->assertObjectHasAttribute('profile', $profile);
        $this->assertObjectHasAttribute('plan', $profile);
    }

    public function testGetCount()
    {
        $query = 'FOR doc IN ' . $this->collection . ' RETURN doc';
        $options = ['count' => true];
        $statement = $this->arangoClient->prepare($query, [], $options);
        $statement->execute();

        $this->assertSame(0, $statement->getCount());
    }

    public function testGetCountNotSet()
    {
        $this->statement->execute();

        $this->assertNull($this->statement->getCount());
    }

    public function testFetchAll()
    {
        $this->generateTestDocuments();

        $query = 'FOR doc IN ' . $this->collection . ' RETURN doc';
        $this->statement->setQuery($query);
        $executed = $this->statement->execute();
        $this->assertTrue($executed);

        $results = $this->statement->fetchAll();
        $this->assertEquals(10, count($results));
        $this->assertSame('test1', $results[0]->_key);
    }


    public function testResultsGreaterThanBatchSize()
    {
        $this->generateTestDocuments();

        // Retrieve data in batches of 2
        $query = 'FOR doc IN ' . $this->collection . ' RETURN doc';
        $options = ['batchSize' => 2];
        $statement = $this->arangoClient->prepare($query, [], $options);
        $executed = $statement->execute();
        $this->assertTrue($executed);
        $results =$statement->fetchAll();

        $this->assertEquals(10, count($results));
        $this->assertSame('test1', $results[0]->_key);
    }

    public function testStatementIsIterable()
    {
        $this->generateTestDocuments();
        $this->statement->execute();

        $count = 0;
        foreach ($this->statement as $document) {
            $this->assertObjectHasAttribute('foobar', $document);
            $count++;
        }
        $this->assertEquals(10, $count);
    }

    public function testGetWritesExecuted(): void
    {
        $query = 'FOR i IN 1..10
          INSERT {
                _key: CONCAT("test", i),
            name: "test",
            foobar: true
          } INTO '.$this->collection.' OPTIONS { ignoreErrors: true }';

        $statement = $this->arangoClient->prepare($query);
        $statement->execute();

        $this->assertSame(10, $statement->getWritesExecuted());
    }
}