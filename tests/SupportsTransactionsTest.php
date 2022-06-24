<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Transactions\TransactionManager;

class SupportsTransactionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testTransactions()
    {
        $transactionManager = $this->arangoClient->transactions();
        $this->assertInstanceOf(TransactionManager::class, $transactionManager);
    }

    public function testBeginTransaction()
    {
        $transactionId = $this->arangoClient->beginTransaction();
        $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();
        $this->assertSame($transactionId, $runningTransactions[0]->id);

        $this->arangoClient->abort();
    }

    public function testBegin()
    {
        $transactionId = $this->arangoClient->begin();
        $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();
        $this->assertSame($transactionId, $runningTransactions[0]->id);

        $this->arangoClient->abort();
    }

    public function testAbort()
    {
        $transactionId = $this->arangoClient->beginTransaction();
        $aborted = $this->arangoClient->abort();
        $this->assertTrue($aborted);

        $transactionsListedInManager = $this->arangoClient->transactions()->getTransactions();
        $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();

        $this->assertArrayNotHasKey($transactionId, $transactionsListedInManager);
        $this->assertFalse(array_search($transactionId, array_column($runningTransactions, 'id')));
    }

    public function testRollBack()
    {
        $transactionId = $this->arangoClient->beginTransaction();
        $aborted = $this->arangoClient->rollBack();
        $this->assertTrue($aborted);

        $transactionsListedInManager = $this->arangoClient->transactions()->getTransactions();
        $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();

        $this->assertArrayNotHasKey($transactionId, $transactionsListedInManager);
        $this->assertFalse(array_search($transactionId, array_column($runningTransactions, 'id')));
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

        $this->arangoClient->beginTransaction($collections);

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

        $this->arangoClient->commit();

        $getQuery = 'for user in Users RETURN user';
        $getStatement = $this->arangoClient->prepare($getQuery);
        $getStatement->execute();

        $this->assertEquals(10, count($getStatement->fetchAll()));

        $this->arangoClient->schema()->deleteCollection('Users');
        $this->arangoClient->schema()->deleteCollection('Customers');
    }

    public function testTransactionManagerSetterAndGetter()
    {
        $oldTransactionManager = $this->arangoClient->getTransactionManager();
        $newTransactionManager = new TransactionManager($this->arangoClient);
        $this->arangoClient->setTransactionManager($newTransactionManager);
        $retrievedNewTransactionManager = $this->arangoClient->getTransactionManager();

        $this->assertNull($oldTransactionManager);
        $this->assertEquals(spl_object_id($newTransactionManager), spl_object_id($retrievedNewTransactionManager));
    }
}
