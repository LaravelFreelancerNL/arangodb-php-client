<?php

declare(strict_types=1);

namespace ArangoClient\Transactions;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Manager;

/**
 * Class TransactionManager
 * Begins transactions and manages those transactions.
 *
 * @see https://www.arangodb.com/docs/stable/http/transaction-stream-transaction.html
 *
 * @package ArangoClient\Transactions
 */
class TransactionManager extends Manager
{
    /**
     * @var ArangoClient
     */
    protected ArangoClient $arangoClient;

    /**
     * @var array<string>
     */
    protected array $transactions = [];

    /**
     * Documents constructor.
     * @param  ArangoClient  $arangoClient
     */
    public function __construct(ArangoClient $arangoClient)
    {
        $this->arangoClient = $arangoClient;

        register_shutdown_function(array($this, 'abortRunningTransactions'));
    }

    /**
     * @return string[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    /**
     * @param  string|null  $id
     * @return string
     * @throws ArangoException
     */
    public function getTransaction(string $id = null): string
    {
        $this->validateId($id);

        if ($id === null) {
            return (string) end($this->transactions);
        }

        return $this->transactions[$id];
    }

    /**
     * Begin a transactions and return its id.
     * @param  array{read?: string[], write?: string[], exclusive?: string[]}  $collections
     * @param  array<mixed>  $options
     * @return string
     * @throws ArangoException
     */
    public function begin(array $collections = [], array $options = []): string
    {
        $options['collections'] = $this->prepareCollections($collections);

        $config = [];
        $config['body'] = $this->arangoClient->jsonEncode($options);
        $results = $this->arangoClient->request('post', '/_api/transaction/begin', $config);

        $id = (string) ((array)$results['result'])['id'];
        $this->transactions[$id] = $id;

        return $id;
    }

    /**
     * @param  string|null  $id
     * @return bool
     * @throws ArangoException
     */
    public function commit(string $id = null): bool
    {
        $id = $this->getTransaction($id);

        $uri =  '/_api/transaction/' . $id;

        $this->arangoClient->request('put', $uri);
        unset($this->transactions[$id]);

        return true;
    }

    /**
     * @param  string|null  $id
     * @return true
     * @throws ArangoException
     */
    public function abort(string $id = null): bool
    {
        $id = $this->getTransaction($id);

        $uri =  '/_api/transaction/' . $id;

        $this->arangoClient->request('delete', $uri);
        unset($this->transactions[$id]);

        return true;
    }

    /**
     * @param  string|null  $id
     * @return bool
     * @throws ArangoException
     */
    protected function validateId(string $id = null): bool
    {
        if (
            empty($this->transactions)
            || (
                $id !== null
                && ! isset($this->transactions[$id])
            )
        ) {
            throw new ArangoException('Transaction not found.', 404);
        }

        return true;
    }

    /**
     * @param  array{read?: string[], write?: string[], exclusive?: string[]}  $collections
     * @return array<string, array<string>>
     */
    protected function prepareCollections(array $collections): array
    {
        $collectionTemplate = [
            'read' => [],
            'write' => [],
            'exclusive' => []
        ];

        return array_intersect_key(array_merge($collectionTemplate, $collections), $collectionTemplate);
    }

    /**
     * Cleanup any open transactions on shutdown as registered in the constructor.
     *
     * @see TransactionManager::__construct
     *
     * @throws ArangoException
     */
    public function abortRunningTransactions(): void
    {
        foreach ($this->transactions as $id) {
            try {
                $this->abort($id);
            } finally {
                // Ignore any errors, this is just for clean up.
            }
        }
    }
}
