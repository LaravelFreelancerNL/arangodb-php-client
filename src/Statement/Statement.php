<?php

namespace ArangoClient\Statement;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Manager;
use ArrayIterator;
use IteratorAggregate;

/**
 * Executes queries on ArangoDB
 *
 * @see https://www.arangodb.com/docs/stable/http/aql-query-cursor.html
 *
 * @package ArangoClient
 *
 * @template-implements \IteratorAggregate<mixed>
 */
class Statement extends Manager implements IteratorAggregate
{
    protected ArangoClient $arangoClient;

    protected string $query;

    /**
     * @var array<scalar>|null
     */
    protected ?array $bindVars;

    /**
     * @var array<mixed>
     */
    protected array $options = [];

    /**
     * @var array<mixed>
     */
    protected array $results = [];

    /**
     * @var array<mixed>
     */
    protected array $stats = [];

    /**
     * @var array<mixed>
     */
    protected array $warnings = [];

    /**
     * @var int|null
     */
    protected ?int $cursorId = null;

    /**
     * @var bool
     */
    protected bool $cursorHasMoreResults = false;

    /**
     * @var int|null
     */
    protected ?int $count = null;

    /**
     * @var array<mixed>
     */
    protected array $extra = [];

    /**
     * Statement constructor.
     * @param  ArangoClient  $arangoClient
     * @param  string  $query
     * @param  array<scalar>  $bindVars
     * @param  array<mixed>  $options
     */
    public function __construct(
        ArangoClient $arangoClient,
        string $query,
        array $bindVars = null,
        array $options = []
    ) {
        $this->arangoClient = $arangoClient;
        $this->query = $query;
        $this->bindVars = $bindVars;
        $this->options = $options;
    }

    /**
     * A statement can be used like an array to access the results.
     *
     * @return ArrayIterator<array-key, mixed>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->results);
    }

    /**
     * @return bool
     * @throws ArangoException
     */
    public function execute(): bool
    {
        $this->results = [];

        $bodyContent = $this->prepareQueryBodyContent();

        $options = [
            'body' => $bodyContent
        ];
        $results = $this->arangoClient->transactionAwareRequest('post', '/_api/cursor', $options);

        $this->handleQueryResults($results);

        $this->requestOutstandingResults($bodyContent);

        return true;
    }

    /**
     * @return array<mixed>
     */
    protected function prepareQueryBodyContent(): array
    {
        $bodyContent = $this->options;
        $bodyContent['query'] = $this->query;
        if (! empty($this->bindVars)) {
            $bodyContent['bindVars'] = $this->bindVars;
        }

        return $bodyContent;
    }

    /**
     * @param  array<mixed>  $results
     */
    protected function handleQueryResults(array $results): void
    {
        $this->results = array_merge($this->results, (array) $results['result']);

        if (isset($results['extra'])) {
            $this->extra = (array) $results['extra'];
        }

        if (array_key_exists('count', $results)) {
            $this->count = (int) $results['count'];
        }

        $this->cursorHasMoreResults = (bool) $results['hasMore'];
        $this->cursorId = $results['hasMore'] ?  (int) $results['id'] : null;
    }

    /**
     * @param  array<mixed>  $body
     * @throws ArangoException
     */
    protected function requestOutstandingResults(array $body): void
    {
        while ($this->cursorHasMoreResults) {
            $uri = '/_api/cursor/' . (string) $this->cursorId;

            $options = [
                'body' => $body
            ];

            $results = $this->arangoClient->request('put', $uri, $options);

            $this->handleQueryResults($results);
        }
    }

    /**
     * Explain the given query
     *
     * @return array<mixed>
     * @throws ArangoException
     */
    public function explain(): array
    {
        $body = $this->prepareQueryBodyContent();
        $options = [
            'body' => $body
        ];

        return $this->arangoClient->request('post', '/_api/explain', $options);
    }

    /**
     * Parse and validate the query, will through an ArangoException if the query is invalid.
     *
     * @return array<mixed>
     * @throws ArangoException
     */
    public function parse(): array
    {
        $body = $this->prepareQueryBodyContent();
        $options = [
            'body' => $body
        ];

        return $this->arangoClient->request('post', '/_api/query', $options);
    }

    /**
     * Execute the query and return performance information on the query.
     * @see https://www.arangodb.com/docs/3.7/aql/execution-and-performance-query-profiler.html
     *
     * @param  int|bool  $mode
     * @return array<mixed>
     * @throws ArangoException
     */
    public function profile($mode = 1): array
    {
        $bodyContent = $this->prepareQueryBodyContent();

        if (! isset($bodyContent['options']) || ! is_array($bodyContent['options'])) {
            $bodyContent['options'] = [];
        }
        $bodyContent['options']['profile'] = $mode;

        $options = [
            'body' => $bodyContent
        ];

        $results = $this->arangoClient->request('post', '/_api/cursor', $options);

        $this->handleQueryResults($results);

        $this->requestOutstandingResults($bodyContent);

        return $this->extra;
    }

    /**
     * Set a query on the statement
     *
     * @param  string  $query
     * @return Statement
     */
    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get the statement's query.
     *
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * Fetch all results.
     *
     * @return array<mixed>
     */
    public function fetchAll(): array
    {
        return $this->results;
    }

    /**
     * Return the total number of results.
     * Useful if not all results have been retrieved from the database yet.
     *
     * @return int|null
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * Return the number writes executed to by the query.
     *
     * @return int
     */
    public function getWritesExecuted(): int
    {
        return (isset($this->extra['stats']['writesExecuted'])) ? (int) $this->extra['stats']['writesExecuted'] : 0;
    }
}
