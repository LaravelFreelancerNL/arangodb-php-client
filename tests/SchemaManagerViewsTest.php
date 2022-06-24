<?php

declare(strict_types=1);

namespace Tests;

class SchemaManagerViewsTest extends TestCase
{
    protected array $view = [
        'name' => 'testViewBasics',
        'type' => 'arangosearch',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->schemaManager->hasView($this->view['name'])) {
            $this->schemaManager->createView($this->view);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->schemaManager->hasView($this->view['name'])) {
            $this->schemaManager->deleteView($this->view['name']);
        }
    }

    public function testGetViews()
    {
        $views = $this->schemaManager->getViews();

        $this->assertSame($this->view['name'], $views[0]->name);
    }

    public function testGetView()
    {
        $view = $this->schemaManager->getView($this->view['name']);

        $this->assertSame($this->view['name'], $view->name);
        $this->assertObjectHasAttribute('type', $view);
        $this->assertObjectHasAttribute('links', $view);
    }

    public function testGetViewProperties()
    {
        $view = $this->schemaManager->getViewProperties($this->view['name']);

        $this->assertSame($this->view['name'], $view->name);
        $this->assertObjectHasAttribute('type', $view);
        $this->assertObjectHasAttribute('links', $view);
    }

    public function testHasView()
    {
        $result = $this->schemaManager->hasView($this->view['name']);
        $this->assertTrue($result);

        $result = $this->schemaManager->hasView('someNoneExistingView');
        $this->assertFalse($result);
    }

    public function testRenameView()
    {
        $newName = 'newName';
        $result = $this->schemaManager->renameView($this->view['name'], $newName);
        $this->assertSame($newName, $result->name);

        $this->schemaManager->deleteView($newName);
    }

    public function testUpdateView()
    {
        $newViewProps = [
            'cleanupIntervalStep' => 3,
            'primarySort' => 'email',
        ];
        $result = $this->schemaManager->updateView($this->view['name'], $newViewProps);

        $this->assertSame(3, $result->cleanupIntervalStep);
    }

    public function testReplaceView()
    {
        $newViewProps = [
            'primarySort' => [[
                'field' => 'email',
                'direction' => 'desc',
            ]],
        ];
        $newView = $this->schemaManager->replaceView($this->view['name'], $newViewProps);

        $this->assertSame($newViewProps['primarySort'][0]['field'], $newView->primarySort[0]->field);
        $this->assertFalse($newView->primarySort[0]->asc);
    }

    public function testCreateAndDeleteView()
    {
        $view = [
            'name' => 'coolnewview',
        ];
        $created = $this->schemaManager->createView($view);
        $this->assertObjectHasAttribute('name', $created);
        $this->assertSame($view['name'], $created->name);

        $deleted = $this->schemaManager->deleteView($view['name']);
        $this->assertTrue($deleted);
    }
}
