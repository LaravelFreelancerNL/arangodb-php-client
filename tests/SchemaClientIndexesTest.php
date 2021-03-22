<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Exceptions\ArangoException;
use GuzzleHttp\Exception\GuzzleException;


class SchemaClientIndexesTest extends TestCase
{
    protected string $collection = 'users';

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->schemaClient->hasCollection($this->collection)) {
            $this->schemaClient->createCollection($this->collection);
        }
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->schemaClient->hasCollection($this->collection)) {
            $this->schemaClient->deleteCollection($this->collection);
        }
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetIndexes()
    {
        $indexes = $this->schemaClient->getIndexes($this->collection);

        $this->assertIsArray($indexes[0]);
        $this->assertArrayHasKey('name', $indexes[0]);
        $this->assertSame('primary', $indexes[0]['name']);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetIndex()
    {
        $indexes = $this->schemaClient->getIndexes($this->collection);
        $indexId = $indexes[0]['id'];

        $index = $this->schemaClient->getIndex($indexId);

        $this->assertArrayHasKey('name', $index);
        $this->assertArrayHasKey('id', $index);
        $this->assertArrayHasKey('fields', $index);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetIndexByName()
    {
        $indexes = $this->schemaClient->getIndexes($this->collection);
        $indexName = $indexes[0]['name'];

        $index = $this->schemaClient->getIndexByName($this->collection, $indexName);

        $this->assertSame($indexName, $index['name']);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testCreateIndex()
    {
        $index = [
            'name' => 'email_persistent_unique',
            'type' => 'persistent',
            'fields' => ['profile.email'],
            'unique' => true,
            'sparse' => false
        ];
        $created = $this->schemaClient->createIndex($this->collection, $index);
        $result = $this->schemaClient->getIndexByName($this->collection, 'email_persistent_unique');

        $this->assertTrue($created);
        $this->assertSame($index['name'], $result['name']);
        $this->assertSame($index['fields'][0], $result['fields'][0]);
        $this->assertSame($index['unique'], $result['unique']);
        $this->assertSame($index['sparse'], $result['sparse']);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testDeleteIndex()
    {
        $index = [
            'name' => 'email_persistent_unique',
            'type' => 'persistent',
            'fields' => ['profile.email'],
            'unique' => true,
            'sparse' => false
        ];
        $created = $this->schemaClient->createIndex($this->collection, $index);
        $found = $this->schemaClient->getIndexByName($this->collection, 'email_persistent_unique');

        $deleted = $this->schemaClient->deleteIndex($found['id']);
        $this->assertTrue($deleted);
        $searchForDeleted =  $this->schemaClient->getIndexByName($this->collection, 'email_persistent_unique');
        $this->assertFalse($searchForDeleted);
    }
}