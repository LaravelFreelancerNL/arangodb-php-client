<?php

declare(strict_types=1);

namespace ArangoClient\Schema;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use stdClass;

/**
 * @see https://www.arangodb.com/docs/stable/http/collection.html
 */
trait ManagesCollections
{
    protected ArangoClient $arangoClient;

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @return array<mixed>
     *
     * @throws ArangoException
     */
    public function getCollections(bool $excludeSystemCollections = false): array
    {
        $results = $this->arangoClient->request(
            'get',
            '/_api/collection',
            [
                'query' => [
                    'excludeSystem' => $excludeSystemCollections,
                ],
            ]
        );

        return (array) $results->result;
    }

    /**
     * Check for collection existence in current DB.
     *
     *
     * @throws ArangoException
     */
    public function hasCollection(string $name): bool
    {
        $results = $this->getCollections();

        return in_array($name, array_column($results, 'name'), true);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/collection-getting.html#return-information-about-a-collection
     *
     * @throws ArangoException
     */
    public function getCollection(string $name): stdClass
    {
        $uri = '/_api/collection/' . $name;

        return $this->arangoClient->request('get', $uri);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/collection-getting.html#read-properties-of-a-collection
     *
     * @throws ArangoException
     */
    public function getCollectionProperties(string $name): stdClass
    {
        $uri = '/_api/collection/' . $name . '/properties';

        return $this->arangoClient->request('get', $uri);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/collection-getting.html#return-number-of-documents-in-a-collection
     *
     * @throws ArangoException
     */
    public function getCollectionWithDocumentCount(string $name): stdClass
    {
        $uri = '/_api/collection/' . $name . '/count';

        return $this->arangoClient->transactionAwareRequest('get', $uri);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/collection-getting.html#return-number-of-documents-in-a-collection
     *
     * @throws ArangoException
     */
    public function getCollectionDocumentCount(string $name): int
    {
        $results = $this->getCollectionWithDocumentCount($name);

        return (int) $results->count;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/collection-getting.html#return-statistics-for-a-collection
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @throws ArangoException
     */
    public function getCollectionStatistics(string $name, bool $details = false): stdClass
    {
        $uri = '/_api/collection/' . $name . '/figures';

        return $this->arangoClient->request(
            'get',
            $uri,
            [
                'query' => [
                    'details' => $details,
                ],
            ]
        );
    }

    /**
     * @param  array<mixed>  $config
     * @param  int|bool|null  $waitForSyncReplication
     * @param  int|bool|null  $enforceReplicationFactor
     *
     * @throws ArangoException
     */
    public function createCollection(
        string $name,
        array $config = [],
        $waitForSyncReplication = null,
        $enforceReplicationFactor = null
    ): stdClass {
        $options = [];
        if (isset($waitForSyncReplication)) {
            $options['query']['waitForSyncReplication'] = (int) $waitForSyncReplication;
        }
        if (isset($enforceReplicationFactor)) {
            $options['query']['enforceReplicationFactor'] = (int) $enforceReplicationFactor;
        }

        $collection = array_merge($config, ['name' => $name]);
        $options['body'] = $collection;

        return $this->arangoClient->request('post', '/_api/collection', $options);
    }

    /**
     * @param  array<mixed>  $config
     * @param  int|bool|null  $waitForSyncReplication
     * @param  int|bool|null  $enforceReplicationFactor
     *
     * @throws ArangoException
     */
    public function createEdgeCollection(
        string $name,
        array $config = [],
        $waitForSyncReplication = null,
        $enforceReplicationFactor = null
    ): stdClass {
        $config['type'] = 3;

        return $this->createCollection($name, $config, $waitForSyncReplication, $enforceReplicationFactor);
    }

    /**
     * @param  array<mixed>  $config
     *
     * @throws ArangoException
     */
    public function updateCollection(string $name, array $config = []): stdClass
    {
        $uri = '/_api/collection/' . $name . '/properties';

        $options = ['body' => $config];

        return $this->arangoClient->request('put', $uri, $options);
    }

    /**
     * @throws ArangoException
     */
    public function renameCollection(string $old, string $new): stdClass
    {
        $uri = '/_api/collection/' . $old . '/rename';

        $options = [
            'body' => [
                'name' => $new,
            ],
        ];

        return $this->arangoClient->request('put', $uri, $options);
    }

    /**
     * @throws ArangoException
     */
    public function truncateCollection(string $name): stdClass
    {
        $uri = '/_api/collection/' . $name . '/truncate';

        return $this->arangoClient->request('put', $uri);
    }

    /**
     * @throws ArangoException
     */
    public function deleteCollection(string $name): bool
    {
        $uri = '/_api/collection/' . $name;

        return (bool) $this->arangoClient->request('delete', $uri);
    }
}
