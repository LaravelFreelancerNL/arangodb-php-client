<?php

declare(strict_types=1);

namespace Tests;

class SchemaManagerIndexesTest extends TestCase
{
    protected string $collection = 'users';

    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->schemaManager->hasCollection($this->collection)) {
            $this->schemaManager->createCollection($this->collection);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->schemaManager->hasCollection($this->collection)) {
            $this->schemaManager->deleteCollection($this->collection);
        }
    }

    public function testGetIndexes()
    {
        $indexes = $this->schemaManager->getIndexes($this->collection);

        $this->assertIsObject($indexes[0]);
        $this->assertObjectHasAttribute('name', $indexes[0]);
        $this->assertSame('primary', $indexes[0]->name);
    }

    public function testGetIndex()
    {
        $indexes = $this->schemaManager->getIndexes($this->collection);
        $indexId = $indexes[0]->id;

        $index = $this->schemaManager->getIndex($indexId);

        $this->assertObjectHasAttribute('name', $index);
        $this->assertObjectHasAttribute('id', $index);
        $this->assertObjectHasAttribute('fields', $index);
    }

    public function testGetIndexByName()
    {
        $indexes = $this->schemaManager->getIndexes($this->collection);
        $indexName = $indexes[0]->name;

        $index = $this->schemaManager->getIndexByName($this->collection, $indexName);

        $this->assertSame($indexName, $index->name);
    }

    public function testCreateIndex()
    {
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
    }

    public function testDeleteIndex()
    {
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
    }
}
