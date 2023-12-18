<?php

declare(strict_types=1);


class SchemaManagerAnalyzersTest extends \Tests\TestCase
{
    protected array $analyzer = [
        'name' => 'testAnalyzerBasics',
        'type' => 'identity',
    ];

    protected function setUp(): void
    {
        \Tests\TestCase::setUp();

        if (!$this->schemaManager->hasAnalyzer($this->analyzer['name'])) {
            $this->schemaManager->createAnalyzer($this->analyzer);
        }
    }

    protected function tearDown(): void
    {
        \Tests\TestCase::tearDown();

        if ($this->schemaManager->hasAnalyzer($this->analyzer['name'])) {
            $this->schemaManager->deleteAnalyzer($this->analyzer['name']);
        }
    }

    public function testGetAnalyzers()
    {
        $analyzers = $this->schemaManager->getAnalyzers();

        $customAnalyzer = end($analyzers);

        $this->assertSame('arangodb_php_client__test::'.$this->analyzer['name'], $customAnalyzer->name);
    }

    public function testGetAnalyzer()
    {
        $analyzer = $this->schemaManager->getAnalyzer($this->analyzer['name']);

        $this->assertSame('arangodb_php_client__test::'.$this->analyzer['name'], $analyzer->name);
        $this->assertObjectHasProperty('type', $analyzer);
    }

    public function testGetAnalyzerWithFullName()
    {
        $analyzer = $this->schemaManager->getAnalyzer('arangodb_php_client__test::'.$this->analyzer['name']);

        $this->assertSame('arangodb_php_client__test::'.$this->analyzer['name'], $analyzer->name);
        $this->assertObjectHasProperty('type', $analyzer);
    }


    public function testHasAnalyzer()
    {
        $result = $this->schemaManager->hasAnalyzer($this->analyzer['name']);
        $this->assertTrue($result);

        $result = $this->schemaManager->hasAnalyzer('someNoneExistingAnalyzer');
        $this->assertFalse($result);
    }

    public function testReplaceAnalyzer()
    {
        $newAnalyzerProps = [
            'name' => 'newAnalyzer',
            'type' => 'identity',
        ];;
        $newAnalyzer = $this->schemaManager->replaceAnalyzer($this->analyzer['name'], $newAnalyzerProps);

        $this->assertSame('arangodb_php_client__test::'.$this->analyzer['name'], $newAnalyzer->name);
    }

    public function testCreateAndDeleteAnalyzer()
    {
        $analyzer = [
            'name' => 'coolnewanalyzer',
            'type' => 'identity',
        ];
        $created = $this->schemaManager->createAnalyzer($analyzer);
        $this->assertObjectHasProperty('name', $created);
        $this->assertSame('arangodb_php_client__test::'.$analyzer['name'], $created->name);

        $deleted = $this->schemaManager->deleteAnalyzer($analyzer['name']);
        $this->assertTrue($deleted);
    }

    public function testDeleteWithFullName()
    {
        $analyzer = [
            'name' => 'coolnewanalyzer',
            'type' => 'identity',
        ];
        $created = $this->schemaManager->createAnalyzer($analyzer);

        $fullName = 'arangodb_php_client__test::'.$analyzer['name'];

        $deleted = $this->schemaManager->deleteAnalyzer($fullName);

        $hasAnalyzer = $this->schemaManager->hasAnalyzer($fullName);
        $this->assertFalse($hasAnalyzer);
    }

}
