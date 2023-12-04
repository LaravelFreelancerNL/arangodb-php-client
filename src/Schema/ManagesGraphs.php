<?php

declare(strict_types=1);

namespace ArangoClient\Schema;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use stdClass;

/**
 * @see https://www.arangodb.com/docs/stable/http/gharial-management.html
 */
trait ManagesGraphs
{
    protected ArangoClient $arangoClient;

    /**
     * @see https://www.arangodb.com/docs/stable/http/gharial-management.html#create-a-graph
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param  array<mixed>  $config
     *
     * @throws ArangoException
     */
    public function createGraph(
        string $name,
        array $config = [],
        bool $waitForSync = false
    ): stdClass {
        $options = [];
        $options['query']['waitForSync'] = (int) $waitForSync;
        $options['body'] = $config;
        $options['body']['name'] = $name;

        $result = $this->arangoClient->request('post', '/_api/gharial', $options);

        return (object) $result->graph;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/gharial-management.html#list-all-graphs
     *
     * @return array<mixed>
     *
     * @throws ArangoException
     */
    public function getGraphs(): array
    {
        $results = $this->arangoClient->request(
            'get',
            '/_api/gharial'
        );

        return (array) $results->graphs;
    }

    /**
     * Check for graph existence in current DB.
     *
     * @throws ArangoException
     */
    public function hasGraph(string $name): bool
    {
        $results = $this->getGraphs();

        return in_array($name, array_column($results, '_key'), true);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/gharial-management.html#get-a-graph
     *
     * @throws ArangoException
     */
    public function getGraph(string $name): stdClass
    {
        $uri = '/_api/gharial/' . $name;

        return (object) $this->arangoClient->request('get', $uri)->graph;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/gharial-management.html#drop-a-graph
     *
     * @throws ArangoException
     */
    public function deleteGraph(string $name): bool
    {
        $uri = '/_api/gharial/' . $name;

        return (bool) $this->arangoClient->request('delete', $uri);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/gharial-management.html#list-vertex-collections
     *
     * @return array<mixed>
     *
     * @throws ArangoException
     */
    public function getGraphVertices(string $name): array
    {
        $uri = '/_api/gharial/' . $name . '/vertex';

        return (array) $this->arangoClient->request('get', $uri)->collections;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/gharial-management.html#add-vertex-collection
     *
     * @throws ArangoException
     */
    public function addGraphVertex(string $name, string $vertex): stdClass
    {
        $uri = '/_api/gharial/' . $name . '/vertex';

        $options = [
            'body' => [
                'collection' => $vertex,
            ],
        ];

        return (object) $this->arangoClient->request('post', $uri, $options)->graph;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/gharial-management.html#add-vertex-collection
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @throws ArangoException
     */
    public function removeGraphVertex(string $name, string $vertex, bool $dropCollection = false): stdClass
    {
        $uri = '/_api/gharial/' . $name . '/vertex/' . $vertex;

        $options = [];
        $options['query']['dropCollection'] = $dropCollection;

        return (object) $this->arangoClient->request('delete', $uri, $options)->graph;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/gharial-management.html#list-edge-definitions
     *
     * @return array<mixed>
     *
     * @throws ArangoException
     */
    public function getGraphEdges(string $name): array
    {
        $uri = '/_api/gharial/' . $name . '/edge';

        return (array) $this->arangoClient->request('get', $uri)->collections;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/gharial-management.html#add-edge-definition
     *
     * @param  array<mixed>  $edgeDefinition
     *
     * @throws ArangoException
     */
    public function addGraphEdge(string $name, array $edgeDefinition): stdClass
    {
        $uri = '/_api/gharial/' . $name . '/edge';

        $options = [
            'body' => $edgeDefinition,
        ];

        return (object) $this->arangoClient->request('post', $uri, $options)->graph;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/gharial-management.html#replace-an-edge-definition
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param  array<mixed>  $edgeDefinition
     *
     * @throws ArangoException
     */
    public function replaceGraphEdge(
        string $name,
        string $edge,
        array $edgeDefinition,
        bool $dropCollection = false,
        bool $waitForSync = false
    ): stdClass {
        $uri = '/_api/gharial/' . $name . '/edge/' . $edge . '#definition';

        $options = [];
        $options['query']['waitForSync'] = $waitForSync;
        $options['query']['dropCollection'] = $dropCollection;
        $options['body'] = $edgeDefinition;

        return (object) $this->arangoClient->request('put', $uri, $options)->graph;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/gharial-management.html#remove-an-edge-definition-from-the-graph
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @throws ArangoException
     */
    public function removeGraphEdge(
        string $name,
        string $edge,
        bool $dropCollection = true,
        bool $waitForSync = false
    ): stdClass {
        $uri = '/_api/gharial/' . $name . '/edge/' . $edge . '#definition';

        $options = [];
        $options['query']['waitForSync'] = $waitForSync;
        $options['query']['dropCollection'] = $dropCollection;

        return (object) $this->arangoClient->request('delete', $uri, $options)->graph;
    }
}
