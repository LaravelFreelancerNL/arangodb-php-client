<?php

declare(strict_types=1);

namespace Tests;

class SchemaManagerCollectionsTest extends TestCase
{
    public function testGetCollectionsBeforeVersion38()
    {
        $this->skipTestOnArangoVersions('3.8', '>=');
        $result = $this->schemaManager->getCollections();

        $this->assertLessThanOrEqual(count($result), 10);
        $this->assertIsObject($result[0]);
    }

    public function testGetCollections()
    {
        $this->skipTestOnArangoVersions('3.8', '<');
        $result = $this->schemaManager->getCollections();

        $this->assertLessThanOrEqual(count($result), 8);
        $this->assertIsObject($result[0]);
    }

    public function testGetCollectionsWithoutSystem()
    {
        $result = $this->schemaManager->getCollections(true);

        $this->assertEmpty($result);
    }

    public function testGetCollection()
    {
        $collections = $this->schemaManager->getCollections();

        $result = $this->schemaManager->getCollection($collections[0]->name);

        $this->assertIsObject($result);
        $this->assertObjectHasAttribute('name', $result);
        $this->assertObjectHasAttribute('isSystem', $result);
    }

    public function testHasCollection()
    {
        $result = $this->schemaManager->hasCollection('_graphs');
        $this->assertTrue($result);

        $result = $this->schemaManager->hasCollection('someNoneExistingCollection');
        $this->assertFalse($result);
    }

    public function testGetCollectionProperties()
    {
        $collections = $this->schemaManager->getCollections();

        $result = $this->schemaManager->getCollectionProperties($collections[0]->name);

        $this->assertIsObject($result);
        $this->assertObjectHasAttribute('name', $result);
        $this->assertObjectHasAttribute('isSystem', $result);
        $this->assertObjectHasAttribute('statusString', $result);
        $this->assertObjectHasAttribute('keyOptions', $result);
    }

    public function testGetCollectionWithDocumentCount()
    {
        $collections = $this->schemaManager->getCollections();

        $result = $this->schemaManager->getCollectionWithDocumentCount($collections[0]->name);

        $this->assertObjectHasAttribute('name', $result);
        $this->assertObjectHasAttribute('isSystem', $result);
        $this->assertObjectHasAttribute('statusString', $result);
        $this->assertObjectHasAttribute('keyOptions', $result);
        $this->assertObjectHasAttribute('count', $result);
        $this->assertIsNumeric($result->count);
    }

    public function testGetCollectionDocumentCount()
    {
        $collections = $this->schemaManager->getCollections();

        $result = $this->schemaManager->getCollectionDocumentCount($collections[0]->name);

        $this->assertIsNumeric($result);
    }

    public function testGetCollectionStatistics()
    {
        $collections = $this->schemaManager->getCollections();

        $result = $this->schemaManager->getCollectionStatistics($collections[0]->name);

        $this->assertObjectHasAttribute('figures', $result);
    }

    public function testGetCollectionStatisticsWithDetails()
    {
        $collections = $this->schemaManager->getCollections();

        $result = $this->schemaManager->getCollectionStatistics($collections[0]->name, true);

        $this->assertObjectHasAttribute('figures', $result);
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
        $this->assertTrue($result->waitForSync);

        $this->schemaManager->deleteCollection($collection);
    }

    public function testRenameCollection()
    {
        $collection = 'users';
        $newName = 'characters';
        $config = [];

        if (! $this->schemaManager->hasCollection($collection)) {
            $this->schemaManager->createCollection($collection, $config);
        }
        if ($this->schemaManager->hasCollection($newName)) {
            $this->schemaManager->deleteCollection($newName);
        }

        $result = $this->schemaManager->renameCollection($collection, $newName);
        $this->assertSame($newName, $result->name);

        $this->schemaManager->deleteCollection($newName);
    }

    public function testTruncateCollection()
    {
        $collection = 'users';
        if (! $this->schemaManager->hasCollection($collection)) {
            $this->schemaManager->createCollection($collection);
        }
        $this->assertSame(0, $this->schemaManager->getCollectionWithDocumentCount($collection)->count);
        $query = 'FOR i IN 1..10
          INSERT {
                _key: CONCAT("test", i),
            name: "test",
            foobar: true
          } INTO '.$collection.' OPTIONS { ignoreErrors: true }';
        $statement = $this->arangoClient->prepare($query);
        $statement->execute();

        $this->assertSame(0, count($statement->fetchAll()));

        $this->schemaManager->truncateCollection($collection);

        $this->assertSame(0, $this->schemaManager->getCollectionWithDocumentCount($collection)->count);
        $this->schemaManager->deleteCollection($collection);
    }

    public function testCreateAndDeleteCollection()
    {
        $collection = 'users';
        $options = [];

        if (! $this->schemaManager->hasCollection($collection)) {
            $result = $this->schemaManager->createCollection($collection, $options);
            $this->assertEquals($collection, $result->name);
        }

        $result = $this->schemaManager->deleteCollection($collection);
        $this->assertTrue($result);
        $this->assertFalse($this->schemaManager->hasCollection($collection));
    }

    public function testCreateCollectionWithOptions()
    {
        $collection = 'users';
        $options = ['waitForSync' => true];

        if (! $this->schemaManager->hasCollection($collection)) {
            $result = $this->schemaManager->createCollection($collection, $options, 1, 1);
        }

        $collectionProperties = $this->schemaManager->getCollectionProperties('users');
        $this->assertTrue($collectionProperties->waitForSync);

        // $waitForSyncReplication & $enforceReplicationFactor are not listed in the properties, so the lack of
        // of an exception somewhat tests these options...

        $result = $this->schemaManager->deleteCollection($collection);
        $this->assertTrue($result);
        $this->assertFalse($this->schemaManager->hasCollection($collection));
    }


    public function testCreateEdgeCollection()
    {
        $collection = 'relationships';

        if ($this->schemaManager->hasCollection($collection)) {
            $this->schemaManager->deleteCollection($collection);
        }

        $result = $this->schemaManager->createEdgeCollection($collection);

        $this->assertEquals($collection, $result->name);
        $this->assertSame(3, $result->type);

        $this->schemaManager->deleteCollection($collection);
    }
}
