<?php

uses(Tests\TestCase::class);

declare(strict_types=1);
beforeEach(function () {
    if (!$this->schemaManager->hasCollection($this->collection)) {
        $this->schemaManager->createCollection($this->collection);
    }
});

afterEach(function () {
    if ($this->schemaManager->hasCollection($this->collection)) {
        $this->schemaManager->deleteCollection($this->collection);
    }
});


test('get indexes', function () {
    $indexes = $this->schemaManager->getIndexes($this->collection);

    $this->assertIsObject($indexes[0]);
    $this->assertObjectHasProperty('name', $indexes[0]);
    $this->assertSame('primary', $indexes[0]->name);
});

test('get index', function () {
    $indexes = $this->schemaManager->getIndexes($this->collection);
    $indexId = $indexes[0]->id;

    $index = $this->schemaManager->getIndex($indexId);

    $this->assertObjectHasProperty('name', $index);
    $this->assertObjectHasProperty('id', $index);
    $this->assertObjectHasProperty('fields', $index);
});

test('get index by name', function () {
    $indexes = $this->schemaManager->getIndexes($this->collection);
    $indexName = $indexes[0]->name;

    $index = $this->schemaManager->getIndexByName($this->collection, $indexName);

    $this->assertSame($indexName, $index->name);
});

test('create index', function () {
    $index = [
        'name' => 'email_persistent_unique',
        'type' => 'persistent',
        'fields' => ['profile.email'],
        'unique' => true,
        'sparse' => false,
    ];
    $created = $this->schemaManager->createIndex($this->collection, $index);
    $result = $this->schemaManager->getIndexByName($this->collection, 'email_persistent_unique');

    $this->assertSame($index['name'], $result->name);
    $this->assertSame($index['fields'][0], $result->fields[0]);
    $this->assertSame($index['unique'], $result->unique);
    $this->assertSame($index['sparse'], $result->sparse);
});

test('delete index', function () {
    $index = [
        'name' => 'email_persistent_unique',
        'type' => 'persistent',
        'fields' => ['profile.email'],
        'unique' => true,
        'sparse' => false,
    ];
    $created = $this->schemaManager->createIndex($this->collection, $index);
    $found = $this->schemaManager->getIndexByName($this->collection, 'email_persistent_unique');

    $deleted = $this->schemaManager->deleteIndex($found->id);
    $this->assertEquals($created->id, $deleted->id);
    $searchForDeleted = $this->schemaManager->getIndexByName($this->collection, 'email_persistent_unique');
    $this->assertFalse($searchForDeleted);
});
