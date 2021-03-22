<?php

namespace ArangoClient\Schema;

use ArangoClient\Connector;
use ArangoClient\Exceptions\ArangoException;
use GuzzleHttp\Exception\GuzzleException;

/*
 * @see https://www.arangodb.com/docs/stable/http/collection.html
 */
trait ManagesCollections
{
    protected Connector $connector;

    /**
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param  bool  $excludeSystemCollections
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function getCollections(bool $excludeSystemCollections = false): array
    {
        return (array) $this->connector->request(
            'get',
            '/_api/collection',
            [
                'query' => [
                    'excludeSystem' => $excludeSystemCollections
                ]
            ]
        );
    }

    /**
     * Check for collection existence in current DB.
     *
     * @param  string  $collection
     * @return bool
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function hasCollection(string $collection): bool
    {
        $collections = $this->getCollections();
        return array_search($collection, array_column($collections, 'name'), true) !== false;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/collection-getting.html#return-information-about-a-collection
     *
     * @param  string  $name
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function getCollection(string $name): array
    {
        $uri = '/_api/collection/' . $name;
        return (array) $this->connector->request('get', $uri);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/collection-getting.html#read-properties-of-a-collection
     *
     * @param  string  $collection
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function getCollectionProperties(string $collection): array
    {
        $uri = '/_api/collection/' . $collection . '/properties';
        return (array) $this->connector->request('get', $uri);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/collection-getting.html#return-number-of-documents-in-a-collection
     *
     * @param  string  $collection
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function getCollectionDocumentCount(string $collection): array
    {
        $uri = '/_api/collection/' . $collection . '/count';
        return (array) $this->connector->request('get', $uri);
    }


    /**
     * @see https://www.arangodb.com/docs/stable/http/collection-getting.html#return-statistics-for-a-collection
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param  string  $collection
     * @param  bool  $details
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function getCollectionStatistics(string $collection, bool $details = false): array
    {
        $uri = '/_api/collection/' . $collection . '/figures';
        return (array) $this->connector->request(
            'get',
            $uri,
            [
                'query' => [
                    'details' => $details
                ]
            ]
        );
    }

    /**
     * @param  string  $collection
     * @param  array<mixed>  $config
     * @param  int|null  $waitForSyncReplication
     * @param  int|null  $enforceReplicationFactor
     * @return bool
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function createCollection(
        string $collection,
        array $config = [],
        $waitForSyncReplication = null,
        $enforceReplicationFactor = null
    ): bool {
        $collection = json_encode((object) array_merge($config, ['name' => $collection]));

        $options = ['body' => $collection];
        if (isset($waitForSyncReplication)) {
            $options['query']['waitForSyncReplication'] = $waitForSyncReplication;
        }
        if (isset($enforceReplicationFactor)) {
            $options['query']['enforceReplicationFactor'] = $enforceReplicationFactor;
        }

        return (bool) $this->connector->request('post', '/_api/collection', $options);
    }

    /**
     * @param  string  $name
     * @param  array<mixed>  $config
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function updateCollection(string $name, array $config = []): array
    {
        $uri = '/_api/collection/' . $name . '/properties';

        $config = json_encode((object) $config);
        $options = ['body' => $config];

        return (array) $this->connector->request('put', $uri, $options);
    }

    /**
     * @param  string  $old
     * @param  string  $new
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function renameCollection(string $old, string $new): array
    {
        $uri = '/_api/collection/' . $old . '/rename';

        $newName = json_encode((object) ['name' => $new]);
        $options = ['body' => $newName];

        return (array) $this->connector->request('put', $uri, $options);
    }

    /**
     * @param  string  $name
     * @return bool
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function deleteCollection(string $name): bool
    {
        $uri = '/_api/collection/' . $name;

        return (bool) $this->connector->request('delete', $uri);
    }
}
