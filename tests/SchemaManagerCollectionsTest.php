<?php

declare(strict_types=1);

uses(Tests\TestCase::class);

test('get collections', function () {
    $result = $this->schemaManager->getCollections();

    expect(8)->toBeLessThanOrEqual(count($result));
    expect($result[0])->toBeObject();
});

test('get collections without system', function () {
    $result = $this->schemaManager->getCollections(true);

    expect($result)->toBeEmpty();
});

test('get collection', function () {
    $collections = $this->schemaManager->getCollections();

    $result = $this->schemaManager->getCollection($collections[0]->name);

    expect($result)->toBeObject();
    expect((array) $result)->toHaveKeys(['globallyUniqueId', 'isSystem', 'status', 'type', 'name', 'id']);
});

test('has collection', function () {
    $result = $this->schemaManager->hasCollection('_graphs');
    expect($result)->toBeTrue();

    $result = $this->schemaManager->hasCollection('someNoneExistingCollection');
    expect($result)->toBeFalse();
});

test('get collection properties', function () {
    $collections = $this->schemaManager->getCollections();

    $result = $this->schemaManager->getCollectionProperties($collections[0]->name);

    expect($result)->toBeObject();
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
    expect($result->count)->toBeNumeric();
});

test('get collection document count', function () {
    $collections = $this->schemaManager->getCollections();

    $result = $this->schemaManager->getCollectionDocumentCount($collections[0]->name);

    expect($result)->toBeNumeric();
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
    expect($result->waitForSync)->toBeTrue();

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
    expect($result->name)->toBe($newName);

    $this->schemaManager->deleteCollection($newName);
});

test('truncate collection', function () {
    $collection = 'users';
    if (!$this->schemaManager->hasCollection($collection)) {
        $this->schemaManager->createCollection($collection);
    }

    expect($this->schemaManager->getCollectionWithDocumentCount($collection)->count)->toBe(0);

    $query = 'FOR i IN 1..10
      INSERT {
            _key: CONCAT("test", i),
        name: "test",
        foobar: true
      } INTO ' . $collection . ' OPTIONS { ignoreErrors: true }';
    $statement = $this->arangoClient->prepare($query);
    $statement->execute();

    expect(count($statement->fetchAll()))->toBe(0);

    $this->schemaManager->truncateCollection($collection);

    expect($this->schemaManager->getCollectionWithDocumentCount($collection)->count)->toBe(0);

    if ($this->schemaManager->hasCollection($collection)) {
        $this->schemaManager->deleteCollection($collection);
    }

});

test('create and delete collection', function () {
    $collection = 'users';
    $options = [];

    if (!$this->schemaManager->hasCollection($collection)) {
        $result = $this->schemaManager->createCollection($collection, $options);
        expect($result->name)->toEqual($collection);
    }

    $result = $this->schemaManager->deleteCollection($collection);
    expect($result)->toBeTrue();
    expect($this->schemaManager->hasCollection($collection))->toBeFalse();
});

test('create collection with options', function () {
    $collection = 'users';
    $options = ['waitForSync' => true];

    if (!$this->schemaManager->hasCollection($collection)) {
        $result = $this->schemaManager->createCollection($collection, $options, 1, 1);
    }

    $collectionProperties = $this->schemaManager->getCollectionProperties('users');
    expect($collectionProperties->waitForSync)->toBeTrue();

    // $waitForSyncReplication & $enforceReplicationFactor are not listed in the properties, so the lack of
    // of an exception somewhat tests these options...

    $result = $this->schemaManager->deleteCollection($collection);
    expect($result)->toBeTrue();
    expect($this->schemaManager->hasCollection($collection))->toBeFalse();
});

test('create edge collection', function () {
    $collection = 'relationships';

    if ($this->schemaManager->hasCollection($collection)) {
        $this->schemaManager->deleteCollection($collection);
    }

    $result = $this->schemaManager->createEdgeCollection($collection);

    expect($result->name)->toEqual($collection);
    expect($result->type)->toBe(3);

    $this->schemaManager->deleteCollection($collection);
});

test('deleteAllCollections', function () {
    $collection1 = 'collection1';
    $collection2 = 'collection2';

    if (!$this->schemaManager->hasCollection($collection1)) {
        $result = $this->schemaManager->createCollection($collection1, []);
        expect($result->name)->toEqual($collection1);
    }

    if (!$this->schemaManager->hasCollection($collection2)) {
        $result = $this->schemaManager->createCollection($collection2, []);
        expect($result->name)->toEqual($collection2);
    }

    $createdCollections = $this->schemaManager->getCollections(true);

    $result = $this->schemaManager->deleteAllCollections();

    $finalCollections = $this->schemaManager->getCollections(true);

    expect($result)->toBeTrue();
    expect(count($createdCollections))->toBe(2);
    expect(count($finalCollections))->toBe(0);
});
