<?php

declare(strict_types=1);

use ArangoClient\Transactions\TransactionManager;

uses(Tests\TestCase::class);
beforeEach(function () {
    $this->transactionManager = new TransactionManager($this->arangoClient);
});


test('begin', function () {
    $transactionId = $this->transactionManager->begin();
    $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();
    expect($runningTransactions[0]->id)->toBe($transactionId);

    $this->transactionManager->abort();
});

test('get transactions', function () {
    $transactions = [];
    $begunTransactions = $this->transactionManager->getTransactions();
    expect($begunTransactions)->toBeEmpty();

    $id = $this->transactionManager->begin();
    $transactions[$id] = $id;
    $id = $this->transactionManager->begin();
    $transactions[$id] = $id;

    $begunTransactions = $this->transactionManager->getTransactions();

    expect($begunTransactions)->toBe($transactions);
});

test('get transaction', function () {
    $transactions = [];
    $transactions[] = $this->transactionManager->begin();
    $transactions[] = $this->transactionManager->begin();

    $lastTransaction = $this->transactionManager->getTransaction();

    expect($lastTransaction)->toBe($transactions[1]);
});

test('get transaction before begin', function () {
    $this->expectExceptionCode(404);
    $this->transactionManager->getTransaction();
});

test('begin multiple transactions', function () {
    $transactions = [];
    $transactions[] = $this->transactionManager->begin();
    $transactions[] = $this->transactionManager->begin();

    $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();
    $transactionsListedInManager = $this->transactionManager->getTransactions();

    foreach ($transactions as $key => $id) {
        expect($transactionsListedInManager)->toContain($id);
        $this->assertNotFalse(array_search($id, array_column($runningTransactions, 'id')));
    }
    expect(count($transactionsListedInManager))->toEqual(count($transactions));

    $this->transactionManager->abortRunningTransactions();
});

test('abort', function () {
    $transactionId = $this->transactionManager->begin();
    $aborted = $this->transactionManager->abort();
    expect($aborted)->toBeTrue();

    $transactionsListedInManager = $this->transactionManager->getTransactions();
    $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();

    $this->assertArrayNotHasKey($transactionId, $transactionsListedInManager);
    expect(array_search($transactionId, array_column($runningTransactions, 'id')))->toBeFalse();
});

test('abort before commit', function () {
    $this->expectExceptionCode(404);
    $this->transactionManager->abort();
});

test('abort wrong id', function () {
    $this->expectExceptionCode(404);
    $this->transactionManager->abort('nonExistingTransaction');
});

test('abort running transactions', function () {
    $transactions = [];
    $transactions[] = $this->transactionManager->begin();
    $transactions[] = $this->transactionManager->begin();

    $this->transactionManager->abortRunningTransactions();

    $transactionsListedInManager = $this->transactionManager->getTransactions();
    $runningTransactions = $this->arangoClient->admin()->getRunningTransactions();

    expect($transactionsListedInManager)->toBeEmpty();
    foreach ($transactions as $id) {
        expect(array_search($id, array_column($runningTransactions, 'id')))->toBeFalse();
    }
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

    expect(count($getStatement->fetchAll()))->toEqual(10);

    $this->transactionManager->commit();

    $getQuery = 'for user in Users RETURN user';
    $getStatement = $this->arangoClient->prepare($getQuery);
    $getStatement->execute();

    expect(count($getStatement->fetchAll()))->toEqual(10);

    $this->arangoClient->schema()->deleteCollection('Users');
    $this->arangoClient->schema()->deleteCollection('Customers');
});
