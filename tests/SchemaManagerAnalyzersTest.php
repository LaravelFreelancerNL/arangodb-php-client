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

    $this->assertSame('arangodb_php_client__test::' . $this->analyzer['name'], $customAnalyzer->name);
});

test('get analyzer', function () {
    $analyzer = $this->schemaManager->getAnalyzer($this->analyzer['name']);

    $this->assertSame('arangodb_php_client__test::' . $this->analyzer['name'], $analyzer->name);
    $this->assertObjectHasProperty('type', $analyzer);
});

test('get analyzer with full name', function () {
    $analyzer = $this->schemaManager->getAnalyzer('arangodb_php_client__test::' . $this->analyzer['name']);

    $this->assertSame('arangodb_php_client__test::' . $this->analyzer['name'], $analyzer->name);
    $this->assertObjectHasProperty('type', $analyzer);
});

test('has analyzer', function () {
    $result = $this->schemaManager->hasAnalyzer($this->analyzer['name']);
    $this->assertTrue($result);

    $result = $this->schemaManager->hasAnalyzer('someNoneExistingAnalyzer');
    $this->assertFalse($result);
});

test('replace analyzer', function () {
    $newAnalyzerProps = [
        'name' => 'newAnalyzer',
        'type' => 'identity',
    ];
    ;
    $newAnalyzer = $this->schemaManager->replaceAnalyzer($this->analyzer['name'], $newAnalyzerProps);

    $this->assertSame('arangodb_php_client__test::' . $this->analyzer['name'], $newAnalyzer->name);
});

test('create and delete analyzer', function () {
    $analyzer = [
        'name' => 'coolnewanalyzer',
        'type' => 'identity',
    ];
    $created = $this->schemaManager->createAnalyzer($analyzer);
    $this->assertObjectHasProperty('name', $created);
    $this->assertSame('arangodb_php_client__test::' . $analyzer['name'], $created->name);

    $deleted = $this->schemaManager->deleteAnalyzer($analyzer['name']);
    $this->assertTrue($deleted);
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
    $this->assertFalse($hasAnalyzer);
});
