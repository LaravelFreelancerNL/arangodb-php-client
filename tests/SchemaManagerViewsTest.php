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

    expect($views[0]->name)->toBe($this->view['name']);
});

test('get view', function () {
    $view = $this->schemaManager->getView($this->view['name']);

    expect($view->name)->toBe($this->view['name']);
    $this->assertObjectHasProperty('type', $view);
    $this->assertObjectHasProperty('links', $view);
});

test('get view properties', function () {
    $view = $this->schemaManager->getViewProperties($this->view['name']);

    expect($view->name)->toBe($this->view['name']);
    $this->assertObjectHasProperty('type', $view);
    $this->assertObjectHasProperty('links', $view);
});

test('has view', function () {
    $result = $this->schemaManager->hasView($this->view['name']);
    expect($result)->toBeTrue();

    $result = $this->schemaManager->hasView('someNoneExistingView');
    expect($result)->toBeFalse();
});

test('rename view', function () {
    $newName = 'newName';
    $result = $this->schemaManager->renameView($this->view['name'], $newName);
    expect($result->name)->toBe($newName);

    $this->schemaManager->deleteView($newName);
});

test('update view', function () {
    $newViewProps = [
        'cleanupIntervalStep' => 3,
        'primarySort' => 'email',
    ];
    $result = $this->schemaManager->updateView($this->view['name'], $newViewProps);

    expect($result->cleanupIntervalStep)->toBe(3);
});

test('replace view', function () {
    $newViewProps = [
        'primarySort' => [[
            'field' => 'email',
            'direction' => 'desc',
        ]],
    ];
    $newView = $this->schemaManager->replaceView($this->view['name'], $newViewProps);

    expect($newView->primarySort[0]->field)->toBe($newViewProps['primarySort'][0]['field']);
    expect($newView->primarySort[0]->asc)->toBeFalse();
});

test('create and delete view', function () {
    $view = [
        'name' => 'coolnewview',
    ];
    $created = $this->schemaManager->createView($view);
    $this->assertObjectHasProperty('name', $created);
    expect($created->name)->toBe($view['name']);

    $deleted = $this->schemaManager->deleteView($view['name']);
    expect($deleted)->toBeTrue();
});
