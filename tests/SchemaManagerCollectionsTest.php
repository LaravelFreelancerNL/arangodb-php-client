<?php

uses(Tests\TestCase::class);

declare(strict_types=1);

test('get collections before version38', function () {
    $this->skipTestOnArangoVersions('3.8', '>=');
    $result = $this->schemaManager->getCollections();

    $this->assertLessThanOrEqual(count($result), 10);
    $this->assertIsObject($result[0]);
});

test('get collections', function () {
    $this->skipTestOnArangoVersions('3.8', '<');
    $result = $this->schemaManager->getCollections();

    $this->assertLessThanOrEqual(count($result), 8);
    $this->assertIsObject($result[0]);
});

test('get collections without system', function () {
    $result = $this->schemaManager->getCollections(true);

    $this->assertEmpty($result);
});

test('get collection', function () {
    $collections = $this->schemaManager->getCollections();

    $result = $this->schemaManager->getCollection($collections[0]->name);

    $this->assertIsObject($result);
    $this->assertObjectHasProperty('name', $result);
    $this->assertObjectHasProperty('isSystem', $result);
});

test('has collection', function () {
    $result = $this->schemaManager->hasCollection('_graphs');
    $this->assertTrue($result);

    $result = $this->schemaManager->hasCollection('someNoneExistingCollection');
    $this->assertFalse($result);
});

test('get collection properties', function () {
    $collections = $this->schemaManager->getCollections();

    $result = $this->schemaManager->getCollectionProperties($collections[0]->name);

    $this->assertIsObject($result);
    $this->assertObjectHasProperty('name', $result);
    $this->assertObjectHasProperty('isSystem', $result);
    $this->assertObjectHasProperty('statusString', $result);
    $this->assertObjectHasProperty('keyOptions', $result);
});

test('get collection with document count', function () {
    $collections = $this->schemaManager->getCollections();

    $result = $this->schemaManager->getCollectionWithDocumentCount($collections[0]->name);

    $this->assertObjectHasProperty('name', $result);
    $this->assertObjectHasProperty('isSystem', $result);
    $this->assertObjectHasProperty('statusString', $result);
    $this->assertObjectHasProperty('keyOptions', $result);
    $this->assertObjectHasProperty('count', $result);
    $this->assertIsNumeric($result->count);
});

test('get collection document count', function () {
    $collections = $this->schemaManager->getCollections();

    $result = $this->schemaManager->getCollectionDocumentCount($collections[0]->name);

    $this->assertIsNumeric($result);
});

test('get collection statistics', function () {
    $collections = $this->schemaManager->getCollections();

    $result = $this->schemaManager->getCollectionStatistics($collections[0]->name);

    $this->assertObjectHasProperty('figures', $result);
});

test('get collection statistics with details', function () {
    $collections = $this->schemaManager->getCollections();

    $result = $this->schemaManager->getCollectionStatistics($collections[0]->name, true);

    $this->assertObjectHasProperty('figures', $result);
});

test('update collection', function () {
    $collection = 'users';
    $config = [];

    if (!$this->schemaManager->hasCollection($collection)) {
        $this->schemaManager->createCollection($collection, $config);
    }

    $newConfig = ['waitForSync' => true];
    $result = $this->schemaManager->updateCollection($collection, $newConfig);
    $this->assertTrue($result->waitForSync);

    $this->schemaManager->deleteCollection($collection);
});

test('rename collection', function () {
    $collection = 'users';
    $newName = 'characters';
    $config = [];

    if (!$this->schemaManager->hasCollection($collection)) {
        $this->schemaManager->createCollection($collection, $config);
    }
    if ($this->schemaManager->hasCollection($newName)) {
        $this->schemaManager->deleteCollection($newName);
    }

    $result = $this->schemaManager->renameCollection($collection, $newName);
    $this->assertSame($newName, $result->name);

    $this->schemaManager->deleteCollection($newName);
});

test('truncate collection', function () {
    $collection = 'users';
    if (!$this->schemaManager->hasCollection($collection)) {
        $this->schemaManager->createCollection($collection);
    }
    $this->assertSame(0, $this->schemaManager->getCollectionWithDocumentCount($collection)->count);
    $query = 'FOR i IN 1..10
      INSERT {
            _key: CONCAT("test", i),
        name: "test",
        foobar: true
      } INTO ' . $collection . ' OPTIONS { ignoreErrors: true }';
    $statement = $this->arangoClient->prepare($query);
    $statement->execute();

    $this->assertSame(0, count($statement->fetchAll()));

    $this->schemaManager->truncateCollection($collection);

    $this->assertSame(0, $this->schemaManager->getCollectionWithDocumentCount($collection)->count);
    $this->schemaManager->deleteCollection($collection);
});

test('create and delete collection', function () {
    $collection = 'users';
    $options = [];

    if (!$this->schemaManager->hasCollection($collection)) {
        $result = $this->schemaManager->createCollection($collection, $options);
        $this->assertEquals($collection, $result->name);
    }

    $result = $this->schemaManager->deleteCollection($collection);
    $this->assertTrue($result);
    $this->assertFalse($this->schemaManager->hasCollection($collection));
});

test('create collection with options', function () {
    $collection = 'users';
    $options = ['waitForSync' => true];

    if (!$this->schemaManager->hasCollection($collection)) {
        $result = $this->schemaManager->createCollection($collection, $options, 1, 1);
    }

    $collectionProperties = $this->schemaManager->getCollectionProperties('users');
    $this->assertTrue($collectionProperties->waitForSync);

    // $waitForSyncReplication & $enforceReplicationFactor are not listed in the properties, so the lack of
    // of an exception somewhat tests these options...

    $result = $this->schemaManager->deleteCollection($collection);
    $this->assertTrue($result);
    $this->assertFalse($this->schemaManager->hasCollection($collection));
});

test('create edge collection', function () {
    $collection = 'relationships';

    if ($this->schemaManager->hasCollection($collection)) {
        $this->schemaManager->deleteCollection($collection);
    }

    $result = $this->schemaManager->createEdgeCollection($collection);

    $this->assertEquals($collection, $result->name);
    $this->assertSame(3, $result->type);

    $this->schemaManager->deleteCollection($collection);
});
