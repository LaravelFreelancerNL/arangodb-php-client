<?php

declare(strict_types=1);

uses(Tests\TestCase::class);

test('create and delete graph', function () {
    $result = $this->schemaManager->createGraph('locations', [], true);
    expect($result->_id)->toBe('_graphs/locations');

    $result = $this->schemaManager->deleteGraph('locations');
    expect($result)->toBeTrue();
});

test('create graph with edges', function () {
    if (!$this->schemaManager->hasCollection('characters')) {
        $this->schemaManager->createCollection('characters');
    }
    $result = $this->schemaManager->createGraph(
        'relations',
        [
            'edgeDefinitions' => [
                [
                    'collection' => 'children',
                    'from' => ['characters'],
                    'to' => ['characters'],
                ],
            ],
            'orphanCollections' => [
                'orphanVertices',
            ],
        ],
        true,
    );
    expect(is_countable($result->edgeDefinitions) ? count($result->edgeDefinitions) : 0)->toEqual(1);
    expect('_graphs/relations')->toEqual($result->_id);

    $this->schemaManager->deleteGraph('relations');
    $this->schemaManager->deleteCollection('children');
    $this->schemaManager->deleteCollection('characters');
    $this->schemaManager->deleteCollection('orphanVertices');
});

test('get graphs no results', function () {
    $result = $this->schemaManager->getGraphs();

    expect(count($result))->toBeLessThanOrEqual(0);
});

test('get graphs with results', function () {
    if (!$this->schemaManager->hasGraph('characters')) {
        $this->schemaManager->createGraph('characters');
    }
    if (!$this->schemaManager->hasGraph('locations')) {
        $this->schemaManager->createGraph('locations');
    }

    $result = $this->schemaManager->getGraphs();

    expect(count($result))->toEqual(2);
    expect($result[0]->_key)->toEqual('characters');
    expect($result[1]->_key)->toEqual('locations');

    $this->schemaManager->deleteGraph('characters');
    $this->schemaManager->deleteGraph('locations');
});

test('has graph', function () {
    if (!$this->schemaManager->hasGraph('locations')) {
        $this->schemaManager->createGraph('locations');
    }
    $result = $this->schemaManager->hasGraph('locations');
    expect($result)->toBeTrue();

    $this->schemaManager->deleteGraph('locations');
    $result = $this->schemaManager->hasGraph('locations');
    expect($result)->toBeFalse();
});

test('get graph', function () {
    if (!$this->schemaManager->hasGraph('locations')) {
        $this->schemaManager->createGraph('locations');
    }

    $result = $this->schemaManager->getGraph('locations');

    expect($result->_key)->toEqual('locations');

    $this->schemaManager->deleteGraph('locations');
});

test('get graph vertices', function () {
    if (!$this->schemaManager->hasCollection('characters')) {
        $this->schemaManager->createCollection('characters');
    }
    if (!$this->schemaManager->hasGraph('relations')) {
        $result = $this->schemaManager->createGraph(
            'relations',
            [
                'edgeDefinitions' => [
                    [
                        'collection' => 'children',
                        'from' => ['characters'],
                        'to' => ['characters'],
                    ],
                ],
                'orphanCollections' => [
                    'orphanVertices',
                ],
            ],
            true,
        );
    }

    $results = $this->schemaManager->getGraphVertices('relations');

    expect(count($results))->toEqual(2);
    expect('characters')->toEqual($results[0]);
    expect('orphanVertices')->toEqual($results[1]);

    $this->schemaManager->deleteGraph('relations');
    $this->schemaManager->deleteCollection('children');
    $this->schemaManager->deleteCollection('characters');
    $this->schemaManager->deleteCollection('orphanVertices');
});

test('add graph vertex', function () {
    if (!$this->schemaManager->hasGraph('relations')) {
        $this->schemaManager->createGraph(
            'relations',
            [
                'edgeDefinitions' => [
                    [
                        'collection' => 'children',
                        'from' => ['characters'],
                        'to' => ['characters'],
                    ],
                ],
                'orphanCollections' => [
                    'orphanVertices',
                ],
            ],
            false,
        );
    }
    $newVertex = 'houses';

    $result = $this->schemaManager->addGraphVertex('relations', $newVertex);

    expect($result->orphanCollections)->toContain('orphanVertices');
    expect($result->orphanCollections)->toContain($newVertex);

    $this->schemaManager->deleteGraph('relations');
    $this->schemaManager->deleteCollection('children');
    $this->schemaManager->deleteCollection('characters');
    $this->schemaManager->deleteCollection('orphanVertices');
    $this->schemaManager->deleteCollection($newVertex);
});

test('remove graph vertex', function () {
    if (!$this->schemaManager->hasGraph('relations')) {
        $this->schemaManager->createGraph(
            'relations',
            [
                'edgeDefinitions' => [
                    [
                        'collection' => 'children',
                        'from' => ['characters'],
                        'to' => ['characters'],
                    ],
                ],
                'orphanCollections' => [
                    'orphanVertices',
                ],
            ],
            false,
        );
    }

    $result = $this->schemaManager->removeGraphVertex('relations', 'orphanVertices', true);

    $this->assertNotContains('orphanVertices', $result->orphanCollections);

    $checkDropped = $this->schemaManager->hasCollection('orphanVertices');
    expect($checkDropped)->toBeFalse();

    $this->schemaManager->deleteGraph('relations');
    $this->schemaManager->deleteCollection('children');
    $this->schemaManager->deleteCollection('characters');
});

test('get graph edges', function () {
    if (!$this->schemaManager->hasCollection('characters')) {
        $this->schemaManager->createCollection('characters');
    }
    if (!$this->schemaManager->hasGraph('relations')) {
        $result = $this->schemaManager->createGraph(
            'relations',
            [
                'edgeDefinitions' => [
                    [
                        'collection' => 'children',
                        'from' => ['characters'],
                        'to' => ['characters'],
                    ],
                ],
                'orphanCollections' => [
                    'orphanVertices',
                ],
            ],
            true,
        );
    }

    $results = $this->schemaManager->getGraphEdges('relations');

    expect(count($results))->toEqual(1);
    expect('children')->toEqual($results[0]);

    $this->schemaManager->deleteGraph('relations');
    $this->schemaManager->deleteCollection('children');
    $this->schemaManager->deleteCollection('characters');
    $this->schemaManager->deleteCollection('orphanVertices');
});

test('add graph edge', function () {
    if (!$this->schemaManager->hasGraph('relations')) {
        $this->schemaManager->createGraph(
            'relations',
            [
                'edgeDefinitions' => [
                    [
                        'collection' => 'children',
                        'from' => ['characters'],
                        'to' => ['characters'],
                    ],
                ],
            ],
            false,
        );
    }
    $newEdge = [
        'collection' => 'vassals',
        'from' => ['characters'],
        'to' => ['houses'],
    ];

    $result = $this->schemaManager->addGraphEdge('relations', $newEdge);

    expect($result->edgeDefinitions[1]->collection)->toEqual($newEdge['collection']);

    $this->schemaManager->deleteGraph('relations');
    $this->schemaManager->deleteCollection('children');
    $this->schemaManager->deleteCollection('characters');
    $this->schemaManager->deleteCollection('vassals');
    $this->schemaManager->deleteCollection('houses');
});

test('replace graph edge', function () {
    if (!$this->schemaManager->hasGraph('relations')) {
        $this->schemaManager->createGraph(
            'relations',
            [
                'edgeDefinitions' => [
                    [
                        'collection' => 'children',
                        'from' => ['characters'],
                        'to' => ['characters'],
                    ],
                ],
            ],
        );
    }

    $newEdge = [
        'collection' => 'children',
        'from' => ['houses'],
        'to' => ['houses'],
    ];

    $result = $this->schemaManager->replaceGraphEdge(
        'relations',
        'children',
        $newEdge,
        false,
        true,
    );

    expect($result->edgeDefinitions[0]->collection)->toEqual($newEdge['collection']);

    $this->schemaManager->deleteGraph('relations');
    $this->schemaManager->deleteCollection('children');
    $this->schemaManager->deleteCollection('houses');
    $this->schemaManager->deleteCollection('characters');
});

test('remove graph edge', function () {
    if (!$this->schemaManager->hasGraph('relations')) {
        $this->schemaManager->createGraph(
            'relations',
            [
                'edgeDefinitions' => [
                    [
                        'collection' => 'children',
                        'from' => ['characters'],
                        'to' => ['characters'],
                    ],
                    [
                        'collection' => 'vassals',
                        'from' => ['houses'],
                        'to' => ['houses'],
                    ],
                ],
            ],
        );
    }

    $result = $this->schemaManager->removeGraphEdge(
        'relations',
        'children',
        true,
        true,
    );

    expect(is_countable($result->edgeDefinitions) ? count($result->edgeDefinitions) : 0)->toEqual(1);
    expect($result->edgeDefinitions[0]->collection)->toEqual('vassals');

    $this->schemaManager->deleteGraph('relations');
    $this->schemaManager->deleteCollection('children');
    $this->schemaManager->deleteCollection('houses');
    $this->schemaManager->deleteCollection('characters');
    $this->schemaManager->deleteCollection('vassals');
});
