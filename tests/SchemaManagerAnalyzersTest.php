<?php

uses(Tests\TestCase::class);

declare(strict_types=1);
beforeEach(function () {
    \Tests\TestCase::setUp();

    if (!$this->schemaManager->hasAnalyzer($this->analyzer['name'])) {
        $this->schemaManager->createAnalyzer($this->analyzer);
    }
});

afterEach(function () {
    \Tests\TestCase::tearDown();

    if ($this->schemaManager->hasAnalyzer($this->analyzer['name'])) {
        $this->schemaManager->deleteAnalyzer($this->analyzer['name']);
    }
});


test('get analyzers', function () {
    $analyzers = $this->schemaManager->getAnalyzers();

    $customAnalyzer = end($analyzers);

    expect($customAnalyzer->name)->toBe('arangodb_php_client__test::' . $this->analyzer['name']);
});

test('get analyzer', function () {
    $analyzer = $this->schemaManager->getAnalyzer($this->analyzer['name']);

    expect($analyzer->name)->toBe('arangodb_php_client__test::' . $this->analyzer['name']);
    $this->assertObjectHasProperty('type', $analyzer);
});

test('get analyzer with full name', function () {
    $analyzer = $this->schemaManager->getAnalyzer('arangodb_php_client__test::' . $this->analyzer['name']);

    expect($analyzer->name)->toBe('arangodb_php_client__test::' . $this->analyzer['name']);
    $this->assertObjectHasProperty('type', $analyzer);
});

test('has analyzer', function () {
    $result = $this->schemaManager->hasAnalyzer($this->analyzer['name']);
    expect($result)->toBeTrue();

    $result = $this->schemaManager->hasAnalyzer('someNoneExistingAnalyzer');
    expect($result)->toBeFalse();
});

test('replace analyzer', function () {
    $newAnalyzerProps = [
        'name' => 'newAnalyzer',
        'type' => 'identity',
    ];
    ;
    $newAnalyzer = $this->schemaManager->replaceAnalyzer($this->analyzer['name'], $newAnalyzerProps);

    expect($newAnalyzer->name)->toBe('arangodb_php_client__test::' . $this->analyzer['name']);
});

test('create and delete analyzer', function () {
    $analyzer = [
        'name' => 'coolnewanalyzer',
        'type' => 'identity',
    ];
    $created = $this->schemaManager->createAnalyzer($analyzer);
    $this->assertObjectHasProperty('name', $created);
    expect($created->name)->toBe('arangodb_php_client__test::' . $analyzer['name']);

    $deleted = $this->schemaManager->deleteAnalyzer($analyzer['name']);
    expect($deleted)->toBeTrue();
});

test('delete with full name', function () {
    $analyzer = [
        'name' => 'coolnewanalyzer',
        'type' => 'identity',
    ];
    $created = $this->schemaManager->createAnalyzer($analyzer);

    $fullName = 'arangodb_php_client__test::' . $analyzer['name'];

    $deleted = $this->schemaManager->deleteAnalyzer($fullName);

    $hasAnalyzer = $this->schemaManager->hasAnalyzer($fullName);
    expect($hasAnalyzer)->toBeFalse();
});
