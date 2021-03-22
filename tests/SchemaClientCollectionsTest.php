<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Exceptions\ArangoException;
use GuzzleHttp\Exception\GuzzleException;


class SchemaClientCollectionsTest extends TestCase
{

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetCollections()
    {
        $result = $this->schemaClient->getCollections();

        $this->assertIsArray($result);
        $this->assertLessThanOrEqual(count($result), 14);
        $this->assertIsArray($result[0]);
    }
    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetCollectionsWithoutSystem()
    {
        $result = $this->schemaClient->getCollections(true);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetCollection()
    {
        $collections = $this->schemaClient->getCollections();

        $result = $this->schemaClient->getCollection($collections[0]['name']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('isSystem', $result);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testHasCollection()
    {
        $result = $this->schemaClient->hasCollection('_fishbowl');
        $this->assertTrue($result);

        $result = $this->schemaClient->hasCollection('someNoneExistingCollection');
        $this->assertFalse($result);

    }



    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetCollectionProperties()
    {
        $collections = $this->schemaClient->getCollections();

        $result = $this->schemaClient->getCollectionProperties($collections[0]['name']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('isSystem', $result);
        $this->assertArrayHasKey('statusString', $result);
        $this->assertArrayHasKey('keyOptions', $result);
    }


    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetCollectionDocumentCount()
    {
        $collections = $this->schemaClient->getCollections();

        $result = $this->schemaClient->getCollectionDocumentCount($collections[0]['name']);

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('isSystem', $result);
        $this->assertArrayHasKey('statusString', $result);
        $this->assertArrayHasKey('keyOptions', $result);
        $this->assertArrayHasKey('count', $result);
        $this->assertIsNumeric($result['count']);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetCollectionStatistics()
    {
        $collections = $this->schemaClient->getCollections();

        $result = $this->schemaClient->getCollectionStatistics($collections[0]['name']);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('figures', $result);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetCollectionStatisticsWithDetails()
    {
        $collections = $this->schemaClient->getCollections();

        $result = $this->schemaClient->getCollectionStatistics($collections[0]['name'], true);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('figures', $result);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testUpdateCollection()
    {
        $collection = 'users';
        $config = [];

        if (! $this->schemaClient->hasCollection($collection)) {
            $this->schemaClient->createCollection($collection, $config);
        }

        $newConfig = ['waitForSync' => true];
        $result = $this->schemaClient->updateCollection($collection, $newConfig);
        $this->assertTrue($result['waitForSync']);

        $this->schemaClient->deleteCollection($collection);
    }


    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testRenameCollection()
    {
        $collection = 'users';
        $config = [];

        if (! $this->schemaClient->hasCollection($collection)) {
            $this->schemaClient->createCollection($collection, $config);
        }

        $newName = 'characters';
        $result = $this->schemaClient->renameCollection($collection, $newName);
        $this->assertSame($newName, $result['name']);

        $this->schemaClient->deleteCollection($newName);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testCreateAndDeleteCollection()
    {
        $collection = 'users';
        $options = [];

        if (! $this->schemaClient->hasCollection($collection)) {
            $result = $this->schemaClient->createCollection($collection, $options);
            $this->assertTrue($result);
        }

        $result = $this->schemaClient->deleteCollection($collection);
        $this->assertTrue($result);
        $this->assertFalse($this->schemaClient->hasCollection($collection));
    }
}