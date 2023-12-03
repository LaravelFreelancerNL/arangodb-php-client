<?php

namespace ArangoClient\Statement;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Manager;
use ArrayIterator;
use IteratorAggregate;
use stdClass;

/**
 * Executes queries on ArangoDB
 *
 * @see https://www.arangodb.com/docs/stable/http/aql-query-cursor.html
 *
 * @template-implements \IteratorAggregate<mixed>
 */
class Statement extends Manager implements IteratorAggregate
{
    /**
     * @var array<mixed>
     */
    protected array $results = [];

    protected ?stdClass $stats = null;

    /**
     * @var array<mixed>
     */
    protected array $warnings = [];

    protected ?int $cursorId = null;

    protected bool $cursorHasMoreResults = false;

    protected ?int $count = null;

    protected ?stdClass $extra = null;

    /**
     * Statement constructor.
     *
     * @param  ?array<mixed>  $bindVars
     * @param  array<mixed>  $options
     */
    public function __construct(protected ArangoClient $arangoClient, protected string $query, protected ?array $bindVars, protected array $options = [])
    {
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
     * @throws ArangoException
     */
    public function execute(): bool
    {
        $this->results = [];

        $bodyContent = $this->prepareQueryBodyContent();

        $options = [
            'body' => $bodyContent,
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

    protected function handleQueryResults(stdClass $results): void
    {
        $this->results = array_merge($this->results, (array) $results->result);

        if (property_exists($results, 'extra') && $results->extra !== null) {
            $this->extra = (object) $results->extra;
        }

        if (property_exists($results, 'count') && $results->count !== null) {
            $this->count = (int) $results->count;
        }

        $this->cursorHasMoreResults = (bool) $results->hasMore;
        $this->cursorId = $results->hasMore ? (int) $results->id : null;
    }

    /**
     * @param  array<mixed>  $body
     *
     * @throws ArangoException
     */
    protected function requestOutstandingResults(array $body): void
    {
        while ($this->cursorHasMoreResults) {
            $uri = '/_api/cursor/'.(string) $this->cursorId;

            $options = [
                'body' => $body,
            ];

            $results = $this->arangoClient->request('put', $uri, $options);

            $this->handleQueryResults($results);
        }
    }

    /**
     * @throws ArangoException
     */
    public function explain(): stdClass
    {
        $body = $this->prepareQueryBodyContent();
        $options = [
            'body' => $body,
        ];

        return $this->arangoClient->request('post', '/_api/explain', $options);
    }

    /**
     * Parse and validate the query, will through an ArangoException if the query is invalid.
     *
     * @throws ArangoException
     */
    public function parse(): stdClass
    {
        $body = $this->prepareQueryBodyContent();
        $options = [
            'body' => $body,
        ];

        return $this->arangoClient->request('post', '/_api/query', $options);
    }

    /**
     * Execute the query and return performance information on the query.
     *
     * @see https://www.arangodb.com/docs/3.7/aql/execution-and-performance-query-profiler.html
     *
     * @throws ArangoException
     */
    public function profile(int|bool $mode = 1): ?stdClass
    {
        $bodyContent = $this->prepareQueryBodyContent();

        if (! isset($bodyContent['options']) || ! is_array($bodyContent['options'])) {
            $bodyContent['options'] = [];
        }
        $bodyContent['options']['profile'] = $mode;

        $options = [
            'body' => $bodyContent,
        ];

        $results = $this->arangoClient->request('post', '/_api/cursor', $options);

        $this->handleQueryResults($results);

        $this->requestOutstandingResults($bodyContent);

        return $this->extra;
    }

    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

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
     * Return the total number of results. (not just the retrieved results)
     * Useful if not all results have been retrieved from the database yet.
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    public function getWritesExecuted(): int
    {
        return (int) $this->extra?->stats?->writesExecuted;
    }
}
