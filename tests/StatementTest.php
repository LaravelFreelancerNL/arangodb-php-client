<?php

declare(strict_types=1);

use Traversable;

uses(Tests\TestCase::class);
beforeEach(function () {
    if (!$this->schemaManager->hasCollection($this->collection)) {
        $this->schemaManager->createCollection($this->collection);
    }
    $query = 'FOR doc IN ' . $this->collection . ' RETURN doc';

    $this->statement = $this->arangoClient->prepare($query);
});

afterEach(function () {
    if ($this->schemaManager->hasCollection($this->collection)) {
        $this->schemaManager->deleteCollection($this->collection);
    }
});


test('set and get query', function () {
    $query = 'FOR doc IN ' . $this->collection . ' LIMIT 1 RETURN doc';

    $statement = $this->statement->setQuery($query);

    $this->assertSame($query, $statement->getQuery());
});

test('explain', function () {
    $explanation = $this->statement->explain();

    $this->assertObjectHasProperty('plan', $explanation);
});

test('parse', function () {
    $parsed = $this->statement->parse();

    $this->assertObjectHasProperty('ast', $parsed);
});

test('profile', function () {
    $profile = $this->statement->profile();
    $this->assertObjectHasProperty('stats', $profile);
    $this->assertObjectHasProperty('profile', $profile);
});

test('profile mode two', function () {
    $profile = $this->statement->profile(2);

    $this->assertObjectHasProperty('stats', $profile);
    $this->assertObjectHasProperty('profile', $profile);
    $this->assertObjectHasProperty('plan', $profile);
});

test('get count', function () {
    $query = 'FOR doc IN ' . $this->collection . ' RETURN doc';
    $options = ['count' => true];
    $statement = $this->arangoClient->prepare($query, [], $options);
    $statement->execute();

    $this->assertSame(0, $statement->getCount());
});

test('get count not set', function () {
    $this->statement->execute();

    $this->assertNull($this->statement->getCount());
});

test('fetch all', function () {
    generateTestDocuments();

    $query = 'FOR doc IN ' . $this->collection . ' RETURN doc';
    $this->statement->setQuery($query);
    $executed = $this->statement->execute();
    $this->assertTrue($executed);

    $results = $this->statement->fetchAll();
    $this->assertEquals(10, is_countable($results) ? count($results) : 0);
    $this->assertSame('test1', $results[0]->_key);
});

test('results greater than batch size', function () {
    generateTestDocuments();

    // Retrieve data in batches of 2
    $query = 'FOR doc IN ' . $this->collection . ' RETURN doc';
    $options = ['batchSize' => 2];
    $statement = $this->arangoClient->prepare($query, [], $options);
    $executed = $statement->execute();
    $this->assertTrue($executed);
    $results = $statement->fetchAll();

    $this->assertEquals(10, count($results));
    $this->assertSame('test1', $results[0]->_key);
});

test('statement is iterable', function () {
    generateTestDocuments();
    $this->statement->execute();

    $count = 0;
    foreach ($this->statement as $document) {
        $this->assertObjectHasProperty('foobar', $document);
        $count++;
    }
    $this->assertEquals(10, $count);
});

test('get writes executed', function () {
    $query = 'FOR i IN 1..10
      INSERT {
            _key: CONCAT("test", i),
        name: "test",
        foobar: true
      } INTO ' . $this->collection . ' OPTIONS { ignoreErrors: true }';

    $statement = $this->arangoClient->prepare($query);
    $statement->execute();

    $this->assertSame(10, $statement->getWritesExecuted());
});

// Helpers
function generateTestDocuments(): void
{
    $query = 'FOR i IN 1..10
      INSERT {
            _key: CONCAT("test", i),
        name: "test",
        foobar: true
      } INTO ' . test()->collection . ' OPTIONS { ignoreErrors: true }';

    $statement = test()->arangoClient->prepare($query);

    $statement->execute();
}
