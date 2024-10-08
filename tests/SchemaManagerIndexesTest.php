<?php

declare(strict_types=1);

uses(Tests\TestCase::class);

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

    expect($indexes[0])->toBeObject();
    $this->assertObjectHasProperty('name', $indexes[0]);
    expect($indexes[0]->name)->toBe('primary');
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

    expect($index->name)->toBe($indexName);
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

    expect($result->name)->toBe($index['name']);
    expect($result->fields[0])->toBe($index['fields'][0]);
    expect($result->unique)->toBe($index['unique']);
    expect($result->sparse)->toBe($index['sparse']);
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
    expect($deleted->id)->toEqual($created->id);
    $searchForDeleted = $this->schemaManager->getIndexByName($this->collection, 'email_persistent_unique');
    expect($searchForDeleted)->toBeFalse();
});
