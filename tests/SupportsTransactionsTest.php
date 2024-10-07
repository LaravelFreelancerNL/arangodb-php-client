<?php

declare(strict_types=1);

use ArangoClient\Transactions\TransactionManager;

uses(Tests\TestCase::class);
beforeEach(function () {
});


test('transactions', function () {
    $transactionManager = $this->arangoClient->transactions();
    expect($transactionManager)->toBeInstanceOf(TransactionManager::class);
});

test('begin transaction', function () {
    $transactionId = $this->arangoClient->beginTransaction();
    $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();
    expect($runningTransactions[0]->id)->toBe($transactionId);

    $this->arangoClient->abort();
});

test('begin', function () {
    $transactionId = $this->arangoClient->begin();
    $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();
    expect($runningTransactions[0]->id)->toBe($transactionId);

    $this->arangoClient->abort();
});

test('abort', function () {
    $transactionId = $this->arangoClient->beginTransaction();
    $aborted = $this->arangoClient->abort();
    expect($aborted)->toBeTrue();

    $transactionsListedInManager = $this->arangoClient->transactions()->getTransactions();
    $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();

    $this->assertArrayNotHasKey($transactionId, $transactionsListedInManager);
    expect(array_search($transactionId, array_column($runningTransactions, 'id')))->toBeFalse();
});

test('roll back', function () {
    $transactionId = $this->arangoClient->beginTransaction();
    $aborted = $this->arangoClient->rollBack();
    expect($aborted)->toBeTrue();

    $transactionsListedInManager = $this->arangoClient->transactions()->getTransactions();
    $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();

    $this->assertArrayNotHasKey($transactionId, $transactionsListedInManager);
    expect(array_search($transactionId, array_column($runningTransactions, 'id')))->toBeFalse();
});

test('commit', function () {
    if (!$this->arangoClient->schema()->hasCollection('Users')) {
        $this->arangoClient->schema()->createCollection('Users');
    }
    if (!$this->arangoClient->schema()->hasCollection('Customers')) {
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

    expect(count($getStatement->fetchAll()))->toEqual(10);

    $this->arangoClient->commit();

    $getQuery = 'for user in Users RETURN user';
    $getStatement = $this->arangoClient->prepare($getQuery);
    $getStatement->execute();

    expect(count($getStatement->fetchAll()))->toEqual(10);

    $this->arangoClient->schema()->deleteCollection('Users');
    $this->arangoClient->schema()->deleteCollection('Customers');
});

test('transaction manager setter and getter', function () {
    $oldTransactionManager = $this->arangoClient->getTransactionManager();
    $newTransactionManager = new TransactionManager($this->arangoClient);
    $this->arangoClient->setTransactionManager($newTransactionManager);
    $retrievedNewTransactionManager = $this->arangoClient->getTransactionManager();

    expect($oldTransactionManager)->toBeNull();
    expect(spl_object_id($retrievedNewTransactionManager))->toEqual(spl_object_id($newTransactionManager));
});
