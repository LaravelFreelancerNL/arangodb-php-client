<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Transactions\TransactionManager;

class TransactionManagerTest extends TestCase
{
    protected TransactionManager $transactionManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionManager = new TransactionManager($this->arangoClient);
    }

    public function testBegin()
    {
        $transactionId = $this->transactionManager->begin();
        $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();
        $this->assertSame($transactionId, $runningTransactions[0]->id);

        $this->transactionManager->abort();
    }

    public function testGetTransactions()
    {
        $begunTransactions = $this->transactionManager->getTransactions();
        $this->assertEmpty($begunTransactions);

        $id = $this->transactionManager->begin();
        $transactions[$id] = $id;
        $id = $this->transactionManager->begin();
        $transactions[$id] = $id;

        $begunTransactions = $this->transactionManager->getTransactions();

        $this->assertSame($transactions, $begunTransactions);
    }

    public function testGetTransaction()
    {
        $transactions = [];
        $transactions[] = $this->transactionManager->begin();
        $transactions[] = $this->transactionManager->begin();

        $lastTransaction = $this->transactionManager->getTransaction();

        $this->assertSame($transactions[1], $lastTransaction);
    }

    public function testGetTransactionBeforeBegin()
    {
        $this->expectExceptionCode(404);
        $this->transactionManager->getTransaction();
    }

    public function testBeginMultipleTransactions()
    {
        $transactions = [];
        $transactions[] = $this->transactionManager->begin();
        $transactions[] = $this->transactionManager->begin();

        $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();
        $transactionsListedInManager = $this->transactionManager->getTransactions();

        foreach ($transactions as $key => $id) {
            $this->assertContains($id, $transactionsListedInManager);
            $this->assertNotFalse(array_search($id, array_column($runningTransactions, 'id')));
        }
        $this->assertEquals(count($transactions), count($transactionsListedInManager));

        $this->transactionManager->abortRunningTransactions();
    }

    public function testAbort()
    {
        $transactionId = $this->transactionManager->begin();
        $aborted = $this->transactionManager->abort();
        $this->assertTrue($aborted);

        $transactionsListedInManager = $this->transactionManager->getTransactions();
        $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();

        $this->assertArrayNotHasKey($transactionId, $transactionsListedInManager);
        $this->assertFalse(array_search($transactionId, array_column($runningTransactions, 'id')));
    }

    public function testAbortBeforeCommit()
    {
        $this->expectExceptionCode(404);
        $this->transactionManager->abort();
    }

    public function testAbortWrongId()
    {
        $this->expectExceptionCode(404);
        $this->transactionManager->abort('nonExistingTransaction');
    }

    public function testAbortRunningTransactions()
    {
        $transactions = [];
        $transactions[] = $this->transactionManager->begin();
        $transactions[] = $this->transactionManager->begin();

        $this->transactionManager->abortRunningTransactions();

        $transactionsListedInManager = $this->transactionManager->getTransactions();
        $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();

        $this->assertEmpty($transactionsListedInManager);
        foreach ($transactions as $id) {
            $this->assertFalse(array_search($id, array_column($runningTransactions, 'id')));
        }
    }

    public function testCommit()
    {
        if (! $this->arangoClient->schema()->hasCollection('Users')) {
            $this->arangoClient->schema()->createCollection('Users');
        }
        if (! $this->arangoClient->schema()->hasCollection('Customers')) {
            $this->arangoClient->schema()->createCollection('Customers');
        }

        $collections = [
            'write' => [
                'Users',
                'Customers',
            ],
        ];

        $this->transactionManager->begin($collections);

        $insertQuery = 'FOR i IN 1..10
          INSERT {
                _key: CONCAT("test", i),
            name: "test",
            foobar: true
          } INTO Users OPTIONS { ignoreErrors: true }';
        $insertStatement = $this->arangoClient->prepare($insertQuery);
        $insertStatement->execute();

        $getQuery = 'for user in Users RETURN user';
        $getStatement = $this->arangoClient->prepare($getQuery);
        $getStatement->execute();

        $this->assertEquals(10, count($getStatement->fetchAll()));

        $this->transactionManager->commit();

        $getQuery = 'for user in Users RETURN user';
        $getStatement = $this->arangoClient->prepare($getQuery);
        $getStatement->execute();

        $this->assertEquals(10, count($getStatement->fetchAll()));

        $this->arangoClient->schema()->deleteCollection('Users');
        $this->arangoClient->schema()->deleteCollection('Customers');
    }
}
