<?php

declare(strict_types=1);


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

    expect($statement->getQuery())->toBe($query);
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

    expect($statement->getCount())->toBe(0);
});

test('get count not set', function () {
    $this->statement->execute();

    expect($this->statement->getCount())->toBeNull();
});

test('fetch all', function () {
    generateTestDocuments();

    $query = 'FOR doc IN ' . $this->collection . ' RETURN doc';
    $this->statement->setQuery($query);
    $executed = $this->statement->execute();
    expect($executed)->toBeTrue();

    $results = $this->statement->fetchAll();
    expect(is_countable($results) ? count($results) : 0)->toEqual(10);
    expect($results[0]->_key)->toBe('test1');
});

test('results greater than batch size', function () {
    generateTestDocuments();

    // Retrieve data in batches of 2
    $query = 'FOR doc IN ' . $this->collection . ' RETURN doc';
    $options = ['batchSize' => 2];
    $statement = $this->arangoClient->prepare($query, [], $options);
    $executed = $statement->execute();
    expect($executed)->toBeTrue();
    $results = $statement->fetchAll();

    expect(count($results))->toEqual(10);
    expect($results[0]->_key)->toBe('test1');
});

test('statement is iterable', function () {
    generateTestDocuments();
    $this->statement->execute();

    $count = 0;
    foreach ($this->statement as $document) {
        $this->assertObjectHasProperty('foobar', $document);
        $count++;
    }
    expect($count)->toEqual(10);
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

    expect($statement->getWritesExecuted())->toBe(10);
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
