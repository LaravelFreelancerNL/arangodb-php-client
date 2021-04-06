<?php

declare(strict_types=1);

namespace ArangoClient\Schema;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;

/**
 * Manage collection indexes
 *
 * @see https://www.arangodb.com/docs/stable/http/indexes.html
 */
trait ManagesIndexes
{
    protected ArangoClient $arangoClient;

    /**
     * @see https://www.arangodb.com/docs/stable/http/indexes-working-with.html#read-all-indexes-of-a-collection
     *
     * @param  string  $collection
     * @return array<mixed>
     * @throws ArangoException
     */
    public function getIndexes(string $collection): array
    {
        $options = [
            'query' => [
                'collection' => $collection
            ]
        ];
        $results = $this->arangoClient->request('get', '/_api/index', $options);
        return (array) $results['indexes'];
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/indexes-working-with.html#read-index
     *
     * @param  string  $id
     * @return array<mixed>
     * @throws ArangoException
     */
    public function getIndex(string $id): array
    {
        $uri = '/_api/index/' . $id;

        return $this->arangoClient->request('get', $uri);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/indexes-working-with.html#read-index
     *
     * @param  string  $collection
     * @param  string  $name
     * @return array<mixed>|bool
     * @throws ArangoException
     */
    public function getIndexByName(string $collection, string $name)
    {
        $indexes = $this->getIndexes($collection);
        $searchResult = array_search($name, array_column($indexes, 'name'));
        if (is_integer($searchResult)) {
            return (array) $indexes[$searchResult];
        }

        return (bool) $searchResult;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/indexes-working-with.html#create-index
     *
     * @param  string  $collection
     * @param  array<mixed>  $index
     * @return bool
     * @throws ArangoException
     */
    public function createIndex(string $collection, array $index): bool
    {
        $indexType = 'persistent';

        if (isset($index['type'])) {
            $indexType = (string) $index['type'];
        }
        $uri = '/_api/index#' . $indexType;

        $options = ['body' => $index];
        $options['query']['collection'] = $collection;

        return (bool) $this->arangoClient->request('post', $uri, $options);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/indexes-working-with.html#delete-index
     *
     * @param  string  $id
     * @return bool
     * @throws ArangoException
     */
    public function deleteIndex(string $id): bool
    {
        $uri = '/_api/index/' . $id;

        return (bool) $this->arangoClient->request('delete', $uri);
    }
}
