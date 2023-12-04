<?php

declare(strict_types=1);

namespace ArangoClient\Schema;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use stdClass;

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
     * @return array<mixed>
     *
     * @throws ArangoException
     */
    public function getIndexes(string $collection): array
    {
        $options = [
            'query' => [
                'collection' => $collection,
            ],
        ];
        $results = $this->arangoClient->request('get', '/_api/index', $options);

        return (array) $results->indexes;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/indexes-working-with.html#read-index
     *
     * @throws ArangoException
     */
    public function getIndex(string $id): stdClass
    {
        $uri = '/_api/index/' . $id;

        return $this->arangoClient->request('get', $uri);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/indexes-working-with.html#read-index
     *
     * @throws ArangoException
     */
    public function getIndexByName(string $collection, string $name): stdClass|false
    {
        $indexes = $this->getIndexes($collection);
        $searchResult = array_search($name, array_column($indexes, 'name'));

        if (is_int($searchResult)) {
            return (object) $indexes[$searchResult];
        }

        return false;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/indexes-working-with.html#create-index
     *
     * @param  array<mixed>  $index
     *
     * @throws ArangoException
     */
    public function createIndex(string $collection, array $index): stdClass
    {
        $indexType = 'persistent';

        if (isset($index['type'])) {
            $indexType = (string) $index['type'];
        }
        $uri = '/_api/index#' . $indexType;

        $options = ['body' => $index];
        $options['query']['collection'] = $collection;

        return $this->arangoClient->request('post', $uri, $options);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/indexes-working-with.html#delete-index
     *
     * @throws ArangoException
     */
    public function deleteIndex(string $id): stdClass
    {
        $uri = '/_api/index/' . $id;

        return $this->arangoClient->request('delete', $uri);
    }
}
