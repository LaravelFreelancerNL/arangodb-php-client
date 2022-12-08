<?php

declare(strict_types=1);

namespace ArangoClient\Transactions;

use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Http\HttpRequestOptions;
use stdClass;

trait SupportsTransactions
{
    /**
     * @var TransactionManager|null
     */
    protected ?TransactionManager $transactionManager = null;

    public function transactions(): TransactionManager
    {
        if (! (property_exists($this, 'transactionManager') && $this->transactionManager !== null)) {
            $this->transactionManager = new TransactionManager($this);
        }

        return $this->transactionManager;
    }

    /**
     * Shortcut to begin() on the transactionManager
     *
     * @param  array<string, array<string>>  $collections
     * @param  array<mixed>  $options
     *
     * @throws ArangoException
     */
    public function begin(array $collections = [], array $options = []): string
    {
        return $this->transactions()->begin($collections, $options);
    }

    /**
     * Shortcut to begin() on the transactionManager
     *
     * @param  array<string, array<string>>  $collections
     * @param  array<mixed>  $options
     *
     * @throws ArangoException
     */
    public function beginTransaction(array $collections = [], array $options = []): string
    {
        return $this->transactions()->begin($collections, $options);
    }

    /**
     * Shortcut to abort() on the transactionManager
     *
     * @param  string|null  $id
     *
     * @throws ArangoException
     */
    public function abort(string $id = null): bool
    {
        return $this->transactions()->abort($id);
    }

    /**
     * Shortcut to abort() on the transactionManager
     *
     * @param  string|null  $id
     *
     * @throws ArangoException
     */
    public function rollBack(string $id = null): bool
    {
        return $this->transactions()->abort($id);
    }

    /**
     * Shortcut to commit() on the transactionManager
     *
     * @param  string|null  $id
     *
     * @throws ArangoException
     */
    public function commit(string $id = null): bool
    {
        return $this->transactions()->commit($id);
    }

    /**
     * @param  array<mixed>|HttpRequestOptions  $options
     *
     * @throws ArangoException
     */
    public function transactionAwareRequest(
        string $method,
        string $uri,
        array|\ArangoClient\Http\HttpRequestOptions $options = [],
        ?string $database = null,
        ?int $transactionId = null
    ): stdClass {
        if (is_array($options)) {
            $options = $this->prepareRequestOptions($options);
        }
        try {
            if (! isset($transactionId)) {
                $transactionId = $this->transactions()->getTransaction();
            }
            $options->addHeader('x-arango-trx-id', $transactionId);
        } finally {
            return $this->request($method, $uri, $options, $database);
        }
    }

    public function setTransactionManager(TransactionManager $transactionManager): void
    {
        $this->transactionManager = $transactionManager;
    }

    public function getTransactionManager(): ?TransactionManager
    {
        return $this->transactionManager;
    }
}
