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
 */
class TransactionManager extends Manager
{
    protected ArangoClient $arangoClient;

    /**
     * @var array<string>
     */
    protected array $transactions = [];

    public function __construct(ArangoClient $arangoClient)
    {
        $this->arangoClient = $arangoClient;

        register_shutdown_function([$this, 'abortRunningTransactions']);
    }

    /**
     * @return string[]
     */
    public function getTransactions(): array
    {
        return $this->transactions;
    }

    /**
     * @throws ArangoException
     */
    public function getTransaction(?string $id = null): string
    {
        $this->validateId($id);

        if ($id === null) {
            return (string) end($this->transactions);
        }

        return $this->transactions[$id];
    }

    /**
     * Begin a transactions and return its id.
     *
     * @param  array<string, array<string>>  $collections
     * @param  array<mixed>  $options
     * @return string
     *
     * @throws ArangoException
     */
    public function begin(array $collections = [], array $options = []): string
    {
        $options['collections'] = $this->prepareCollections($collections);

        $config = ['body' => $options];
        $result = (object) $this->arangoClient->request('post', '/_api/transaction/begin', $config)->result;

        $id = (string) $result->id;
        $this->transactions[$id] = $id;

        return $id;
    }

    /**
     * @throws ArangoException
     */
    public function commit(?string $id = null): bool
    {
        $id = $this->getTransaction($id);

        $uri = '/_api/transaction/'.$id;

        $this->arangoClient->request('put', $uri);
        unset($this->transactions[$id]);

        return true;
    }

    /**
     * @throws ArangoException
     */
    public function abort(?string $id = null): bool
    {
        $id = $this->getTransaction($id);

        $uri = '/_api/transaction/'.$id;

        $this->arangoClient->request('delete', $uri);
        unset($this->transactions[$id]);

        return true;
    }

    /**
     * @throws ArangoException
     */
    protected function validateId(?string $id = null): bool
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
     * @param  array<string, array<string>>  $collections
     * @return array<string, array<string>>
     */
    protected function prepareCollections(array $collections): array
    {
        $collectionTemplate = [
            'read' => [],
            'write' => [],
            'exclusive' => [],
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
