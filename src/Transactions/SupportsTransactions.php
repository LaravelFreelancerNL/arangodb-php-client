<?php

declare(strict_types=1);

namespace ArangoClient\Transactions;

use ArangoClient\Exceptions\ArangoException;

trait SupportsTransactions
{
    /**
     * @var TransactionManager|null
     */
    protected ?TransactionManager $transactionManager = null;

    /**
     * @return TransactionManager
     */
    public function transactions(): TransactionManager
    {
        if (! isset($this->transactionManager)) {
            $this->transactionManager = new TransactionManager($this);
        }
        return $this->transactionManager;
    }

    /**
     * Shortcut to begin() on the transactionManager
     *
     * @param  array{read?: string[], write?: string[], exclusive?: string[]}  $collections
     * @param  array<mixed>  $options
     * @return string
     * @throws ArangoException
     */
    public function begin(array $collections = [], array $options = []): string
    {
        return $this->transactions()->begin($collections, $options);
    }

    /**
     * Shortcut to begin() on the transactionManager
     *
     * @param  array{read?: string[], write?: string[], exclusive?: string[]}  $collections
     * @param  array<mixed>  $options
     * @return string
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
     * @return bool
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
     * @return bool
     * @throws ArangoException
     */
    public function rollBack(string $id = null): bool
    {
        return $this->transactions()->abort($id);
    }

    /**
     * Shortcut to commit() on the transactionManager
     * @param  string|null  $id
     * @return bool
     * @throws ArangoException
     */
    public function commit(string $id = null): bool
    {
        return $this->transactions()->commit($id);
    }

    /**
     * @psalm-suppress MixedReturnStatement
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array<mixed>  $options
     * @param  string|null  $database
     * @param  int|null  $transactionId
     * @return array<mixed>
     * @throws ArangoException
     */
    public function transactionAwareRequest(
        string $method,
        string $uri,
        array $options = [],
        ?string $database = null,
        ?int $transactionId = null
    ): array {
        try {
            if (! isset($transactionId)) {
                $transactionId = $this->transactions()->getTransaction();
            }
            if (! isset($options['headers'])) {
                $options['headers'] = [];
            }
            if (! is_array($options['headers'])) {
                $options['headers'] = [$options['headers']];
            }
            $options['headers']['x-arango-trx-id'] = $transactionId;
        } finally {
            return $this->request($method, $uri, $options, $database);
        }
    }
}
