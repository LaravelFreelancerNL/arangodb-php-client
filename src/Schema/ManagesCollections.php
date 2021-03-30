<?php

declare(strict_types=1);

namespace ArangoClient\Schema;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;

/*
 * @see https://www.arangodb.com/docs/stable/http/collection.html
 */
trait ManagesCollections
{
    protected ArangoClient $arangoClient;

    /**
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param  bool  $excludeSystemCollections
     * @return array<mixed>
     * @throws ArangoException
     */
    public function getCollections(bool $excludeSystemCollections = false): array
    {
        $results = $this->arangoClient->request(
            'get',
            '/_api/collection',
            [
                'query' => [
                    'excludeSystem' => $excludeSystemCollections
                ]
            ]
        );

        return (array) $results['result'];
    }

    /**
     * Check for collection existence in current DB.
     *
     * @param  string  $collection
     * @return bool
     * @throws ArangoException
     */
    public function hasCollection(string $collection): bool
    {
        $results = $this->getCollections();
        return array_search($collection, array_column($results, 'name'), true) !== false;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/collection-getting.html#return-information-about-a-collection
     *
     * @param  string  $name
     * @return array<mixed>
     * @throws ArangoException
     */
    public function getCollection(string $name): array
    {
        $uri = '/_api/collection/' . $name;
        return $this->arangoClient->request('get', $uri);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/collection-getting.html#read-properties-of-a-collection
     *
     * @param  string  $collection
     * @return array<mixed>
     * @throws ArangoException
     */
    public function getCollectionProperties(string $collection): array
    {
        $uri = '/_api/collection/' . $collection . '/properties';
        return $this->arangoClient->request('get', $uri);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/collection-getting.html#return-number-of-documents-in-a-collection
     *
     * @param  string  $collection
     * @return array<mixed>
     * @throws ArangoException
     */
    public function getCollectionWithDocumentCount(string $collection): array
    {
        $uri = '/_api/collection/' . $collection . '/count';
        return $this->arangoClient->transactionAwareRequest('get', $uri);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/collection-getting.html#return-number-of-documents-in-a-collection
     *
     * @param  string  $collection
     * @return int
     * @throws ArangoException
     */
    public function getCollectionDocumentCount(string $collection): int
    {
        $results = $this->getCollectionWithDocumentCount($collection);

        return (int) $results['count'];
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
     */
    public function getCollectionStatistics(string $collection, bool $details = false): array
    {
        $uri = '/_api/collection/' . $collection . '/figures';
        return $this->arangoClient->request(
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

        return (bool) $this->arangoClient->request('post', '/_api/collection', $options);
    }

    /**
     * @param  string  $name
     * @param  array<mixed>  $config
     * @return array<mixed>
     * @throws ArangoException
     */
    public function updateCollection(string $name, array $config = []): array
    {
        $uri = '/_api/collection/' . $name . '/properties';

        $config = json_encode((object) $config);
        $options = ['body' => $config];

        return $this->arangoClient->request('put', $uri, $options);
    }

    /**
     * @param  string  $old
     * @param  string  $new
     * @return array<mixed>
     * @throws ArangoException
     */
    public function renameCollection(string $old, string $new): array
    {
        $uri = '/_api/collection/' . $old . '/rename';

        $newName = json_encode((object) ['name' => $new]);
        $options = ['body' => $newName];

        return $this->arangoClient->request('put', $uri, $options);
    }

    /**
     * @param  string  $name
     * @return bool
     * @throws ArangoException
     */
    public function truncateCollection(string $name): bool
    {
        $uri = '/_api/collection/' . $name . '/truncate';

        return (bool) $this->arangoClient->request('put', $uri);
    }

    /**
     * @param  string  $name
     * @return bool
     * @throws ArangoException
     */
    public function deleteCollection(string $name): bool
    {
        $uri = '/_api/collection/' . $name;

        return (bool) $this->arangoClient->request('delete', $uri);
    }
}
