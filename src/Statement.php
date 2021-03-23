<?php

namespace ArangoClient;

use Generator;

/**
 * Executes queries on ArangoDB
 *
 * @see https://www.arangodb.com/docs/stable/http/aql-query-cursor.html
 *
 * @package ArangoClient
 */
class Statement extends Manager
{
    protected ArangoClient $arangoClient;

    protected string $query;

    /**
     * @var array<scalar>
     */
    protected array $bindVars = [];

    /**
     * @var array<array>
     */
    protected array $collections = [];

    /**
     * @var array<scalar>
     */
    protected array $options = [];

    /**
     * @var array<mixed>
     */
    protected array $queryResults = [];

    /**
     * @var array<mixed>
     */
    protected array $queryStatistics = [];

    /**
     * @var array<mixed>
     */
    protected array $queryWarnings = [];

    /**
     * @var array<mixed>
     */
    protected array $cursor = [];

    /**
     * Statement constructor.
     * @param  ArangoClient  $arangoClient
     * @param  string  $query
     * @param  array<scalar>  $bindVars
     * @param  array<array>  $collections
     * @param  array<scalar>  $options
     */
    public function __construct(
        ArangoClient $arangoClient,
        string $query,
        array $bindVars = [],
        array $collections = [],
        array $options = []
    ) {
        $this->arangoClient = $arangoClient;
        $this->query = $query;
        $this->bindVars = $bindVars;
        $this->collections = $collections;
        $this->options = $options;
    }

    /**
     * @return bool
     * @throws Exceptions\ArangoException
     */
    public function execute(): bool
    {
        $this->queryResults = [];

        $bodyContent = $this->prepareQueryBodyContent();
        $body = $this->arangoClient->jsonEncode($bodyContent);

        $results = $this->arangoClient->request('post', '/_api/cursor', ['body' => $body]);

        $this->handleQueryResults($results);

        $this->requestOutstandingResults($body);

        return ! $results['error'];
    }

    /**
     * @return array<mixed>
     */
    protected function prepareQueryBodyContent(): array
    {
        $bodyContent = $this->options;
        $bodyContent['query'] = $this->query;
        $bodyContent['bindVars'] = $this->bindVars;
        $bodyContent['collections'] = $this->collections;

        return $bodyContent;
    }

    /**
     * @param  array<mixed>  $results
     */
    protected function handleQueryResults(array $results): void
    {
        $this->queryResults = array_merge($this->queryResults, (array) $results['result']);

        if (isset($results['extra']['stats'])) {
            $this->queryStatistics = (array)((array)$results['extra'])['stats'];
        }
        if (isset($results['extra']['warnings'])) {
            $this->queryWarnings = (array) ((array)$results['extra'])['warnings'];
        }
        $this->cursor['hasMore'] = (bool) $results['hasMore'];
        $this->cursor['id'] = $results['hasMore'] ?  $results['id'] : null;
    }

    /**
     * @param  string  $body
     * @throws Exceptions\ArangoException
     */
    public function requestOutstandingResults(string $body): void
    {
        while ($this->cursor['hasMore']) {
            $uri = '/_api/cursor/' . (string) $this->cursor['id'];

            $results = $this->arangoClient->request('put', $uri, ['body' => $body]);

            $this->handleQueryResults($results);
        }
    }

    /**
     * @return array<mixed>
     * @throws Exceptions\ArangoException
     */
    public function explain(): array
    {
        $bodyContent = $this->prepareQueryBodyContent();
        $body = $this->arangoClient->jsonEncode($bodyContent);

        $results = $this->arangoClient->request('post', '/_api/explain', ['body' => $body]);

        return $this->sanitizeRequestMetadata($results);
    }

    /**
     * @return array<mixed>
     * @throws Exceptions\ArangoException
     */
    public function parse(): array
    {
        $bodyContent = $this->prepareQueryBodyContent();
        $body = $this->arangoClient->jsonEncode($bodyContent);

        $results = $this->arangoClient->request('post', '/_api/query', ['body' => $body]);

        return $this->sanitizeRequestMetadata($results);
    }

    /**
     * @param  string  $query
     * @return Statement
     */
    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    // phpmd barfs on the return yield from lin
//    /**
//     * @return Generator<mixed>
//     */
//    public function fetch(): Generator
//    {
//        return yield from $this->queryResults;
//    }

    /**
     * @return array<mixed>
     */
    public function fetchAll(): array
    {
        return $this->queryResults;
    }
}
