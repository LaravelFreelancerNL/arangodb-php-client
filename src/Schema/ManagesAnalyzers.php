<?php

declare(strict_types=1);

namespace ArangoClient\Schema;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use stdClass;

/*
 * @see https://docs.arangodb.com/stable/develop/http-api/analyzers/
 */
trait ManagesAnalyzers
{
    protected ArangoClient $arangoClient;

    protected function getFullName(string $name): string
    {
        if (!str_contains($name, '::')) {
            $name = $this->arangoClient->getDatabase().'::'.$name;
        }
        return $name;
    }

    /**
     * @see https://docs.arangodb.com/stable/develop/http-api/analyzers/#create-an-analyzer
     *
     * @param array<mixed> $analyzer
     * @return stdClass
     * @throws ArangoException
     */
    public function createAnalyzer(array $analyzer): stdClass
    {
        $uri = '/_api/analyzer';

        $options = [
            'body' => $analyzer,
        ];

        return $this->arangoClient->request('post', $uri, $options);
    }

    /**
     * @see https://docs.arangodb.com/stable/develop/http-api/analyzers/#remove-an-analyzer
     *
     * @throws ArangoException
     */
    public function deleteAnalyzer(string $name): bool
    {
        $uri = '/_api/analyzer/' . $name;

        return (bool) $this->arangoClient->request('delete', $uri);
    }

    /**
     * @see https://docs.arangodb.com/stable/develop/http-api/analyzers/#list-all-analyzers
     *
     * @return array<mixed>
     *
     * @throws ArangoException
     */
    public function getAnalyzers(): array
    {
        $results = $this->arangoClient->request('get', '/_api/analyzer');

        return (array) $results->result;
    }

    /**
     * Check for analyzer existence
     *
     *
     * @throws ArangoException
     */
    public function hasAnalyzer(string $name): bool
    {
        $name = $this->getFullName($name);

        $analyzers = $this->getAnalyzers();

        return in_array($name, array_column($analyzers, 'name'), true);
    }

    /**
     * @see https://docs.arangodb.com/stable/develop/http-api/analyzers/#get-an-analyzer-definition
     *
     * @throws ArangoException
     */
    public function getAnalyzer (string $name): stdClass
    {
        $uri = '/_api/analyzer/' . $name;

        return $this->arangoClient->request('get', $uri);
    }

    /**
     * Replace an existing analyzer. Note that this is just a shorthand for delete(old)/create(new).
     *
     * @see https://docs.arangodb.com/stable/develop/http-api/analyzers/#create-an-analyzer
     *
     * @param string $name
     * @param array<mixed> $newAnalyzer
     * @return stdClass|false
     * @throws ArangoException
     */
    public function replaceAnalyzer(string $name, array $newAnalyzer): stdClass|false
    {
        if (!$this->hasAnalyzer($name)) {
            return false;
        }
        $this->deleteAnalyzer($name);

        // Enforce the analyzer name
        $newAnalyzer['name'] = $name;

        return $this->createAnalyzer($newAnalyzer);
    }
}
