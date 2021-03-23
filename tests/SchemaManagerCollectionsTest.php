<?php

declare(strict_types=1);

namespace Tests;

class SchemaManagerCollectionsTest extends TestCase
{

    public function testGetCollections()
    {
        $result = $this->schemaManager->getCollections();

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(count($result), 14);
        $this->assertIsArray($result[0]);
    }

    public function testGetCollectionsWithoutSystem()
    {
        $result = $this->schemaManager->getCollections(true);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testGetCollection()
    {
        $collections = $this->schemaManager->getCollections();

        $result = $this->schemaManager->getCollection($collections[0]['name']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('isSystem', $result);
    }

    public function testHasCollection()
    {
        $result = $this->schemaManager->hasCollection('_fishbowl');
        $this->assertTrue($result);

        $result = $this->schemaManager->hasCollection('someNoneExistingCollection');
        $this->assertFalse($result);

    }

    public function testGetCollectionProperties()
    {
        $collections = $this->schemaManager->getCollections();

        $result = $this->schemaManager->getCollectionProperties($collections[0]['name']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('isSystem', $result);
        $this->assertArrayHasKey('statusString', $result);
        $this->assertArrayHasKey('keyOptions', $result);
    }

    public function testGetCollectionDocumentCount()
    {
        $collections = $this->schemaManager->getCollections();

        $result = $this->schemaManager->getCollectionDocumentCount($collections[0]['name']);

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('isSystem', $result);
        $this->assertArrayHasKey('statusString', $result);
        $this->assertArrayHasKey('keyOptions', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertIsNumeric($result['count']);
    }

    public function testGetCollectionStatistics()
    {
        $collections = $this->schemaManager->getCollections();

        $result = $this->schemaManager->getCollectionStatistics($collections[0]['name']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('figures', $result);
    }

    public function testGetCollectionStatisticsWithDetails()
    {
        $collections = $this->schemaManager->getCollections();

        $result = $this->schemaManager->getCollectionStatistics($collections[0]['name'], true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('figures', $result);
    }

    public function testUpdateCollection()
    {
        $collection = 'users';
        $config = [];

        if (! $this->schemaManager->hasCollection($collection)) {
            $this->schemaManager->createCollection($collection, $config);
        }

        $newConfig = ['waitForSync' => true];
        $result = $this->schemaManager->updateCollection($collection, $newConfig);
        $this->assertTrue($result['waitForSync']);

        $this->schemaManager->deleteCollection($collection);
    }

    public function testRenameCollection()
    {
        $collection = 'users';
        $config = [];

        if (! $this->schemaManager->hasCollection($collection)) {
            $this->schemaManager->createCollection($collection, $config);
        }

        $newName = 'characters';
        $result = $this->schemaManager->renameCollection($collection, $newName);
        $this->assertSame($newName, $result['name']);

        $this->schemaManager->deleteCollection($newName);
    }

    public function testCreateAndDeleteCollection()
    {
        $collection = 'users';
        $options = [];

        if (! $this->schemaManager->hasCollection($collection)) {
            $result = $this->schemaManager->createCollection($collection, $options);
            $this->assertTrue($result);
        }

        $result = $this->schemaManager->deleteCollection($collection);
        $this->assertTrue($result);
        $this->assertFalse($this->schemaManager->hasCollection($collection));
    }
}