<?php

uses(Tests\TestCase::class);

declare(strict_types=1);
beforeEach(function () {
    if (!$this->schemaManager->hasView($this->view['name'])) {
        $this->schemaManager->createView($this->view);
    }
});

afterEach(function () {
    if ($this->schemaManager->hasView($this->view['name'])) {
        $this->schemaManager->deleteView($this->view['name']);
    }
});


test('get views', function () {
    $views = $this->schemaManager->getViews();

    $this->assertSame($this->view['name'], $views[0]->name);
});

test('get view', function () {
    $view = $this->schemaManager->getView($this->view['name']);

    $this->assertSame($this->view['name'], $view->name);
    $this->assertObjectHasProperty('type', $view);
    $this->assertObjectHasProperty('links', $view);
});

test('get view properties', function () {
    $view = $this->schemaManager->getViewProperties($this->view['name']);

    $this->assertSame($this->view['name'], $view->name);
    $this->assertObjectHasProperty('type', $view);
    $this->assertObjectHasProperty('links', $view);
});

test('has view', function () {
    $result = $this->schemaManager->hasView($this->view['name']);
    $this->assertTrue($result);

    $result = $this->schemaManager->hasView('someNoneExistingView');
    $this->assertFalse($result);
});

test('rename view', function () {
    $newName = 'newName';
    $result = $this->schemaManager->renameView($this->view['name'], $newName);
    $this->assertSame($newName, $result->name);

    $this->schemaManager->deleteView($newName);
});

test('update view', function () {
    $newViewProps = [
        'cleanupIntervalStep' => 3,
        'primarySort' => 'email',
    ];
    $result = $this->schemaManager->updateView($this->view['name'], $newViewProps);

    $this->assertSame(3, $result->cleanupIntervalStep);
});

test('replace view', function () {
    $newViewProps = [
        'primarySort' => [[
            'field' => 'email',
            'direction' => 'desc',
        ]],
    ];
    $newView = $this->schemaManager->replaceView($this->view['name'], $newViewProps);

    $this->assertSame($newViewProps['primarySort'][0]['field'], $newView->primarySort[0]->field);
    $this->assertFalse($newView->primarySort[0]->asc);
});

test('create and delete view', function () {
    $view = [
        'name' => 'coolnewview',
    ];
    $created = $this->schemaManager->createView($view);
    $this->assertObjectHasProperty('name', $created);
    $this->assertSame($view['name'], $created->name);

    $deleted = $this->schemaManager->deleteView($view['name']);
    $this->assertTrue($deleted);
});
