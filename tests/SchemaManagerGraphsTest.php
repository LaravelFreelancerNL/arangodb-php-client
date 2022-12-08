<?php

declare(strict_types=1);

namespace Tests;

class SchemaManagerGraphsTest extends TestCase
{
    public function testCreateAndDeleteGraph()
    {
        $result = $this->schemaManager->createGraph('locations', [], true);
        $this->assertSame('_graphs/locations', $result->_id);

        $result = $this->schemaManager->deleteGraph('locations');
        $this->assertTrue($result);
    }

    public function testCreateGraphWithEdges()
    {
        if (! $this->schemaManager->hasCollection('characters')) {
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
            true);
        $this->assertEquals(1, is_countable($result->edgeDefinitions) ? count($result->edgeDefinitions) : 0);
        $this->assertEquals($result->_id, '_graphs/relations');

        $this->schemaManager->deleteGraph('relations');
        $this->schemaManager->deleteCollection('children');
        $this->schemaManager->deleteCollection('characters');
        $this->schemaManager->deleteCollection('orphanVertices');
    }

    public function testGetGraphsNoResults()
    {
        $result = $this->schemaManager->getGraphs();

        $this->assertLessThanOrEqual(0, count($result));
    }

    public function testGetGraphsWithResults()
    {
        if (! $this->schemaManager->hasGraph('characters')) {
            $this->schemaManager->createGraph('characters');
        }
        if (! $this->schemaManager->hasGraph('locations')) {
            $this->schemaManager->createGraph('locations');
        }

        $result = $this->schemaManager->getGraphs();

        $this->assertEquals(2, count($result));
        $this->assertEquals('characters', $result[0]->_key);
        $this->assertEquals('locations', $result[1]->_key);

        $this->schemaManager->deleteGraph('characters');
        $this->schemaManager->deleteGraph('locations');
    }

    public function testHasGraph()
    {
        if (! $this->schemaManager->hasGraph('locations')) {
            $this->schemaManager->createGraph('locations');
        }
        $result = $this->schemaManager->hasGraph('locations');
        $this->assertTrue($result);

        $this->schemaManager->deleteGraph('locations');
        $result = $this->schemaManager->hasGraph('locations');
        $this->assertFalse($result);
    }

    public function testGetGraph()
    {
        if (! $this->schemaManager->hasGraph('locations')) {
            $this->schemaManager->createGraph('locations');
        }

        $result = $this->schemaManager->getGraph('locations');

        $this->assertEquals('locations', $result->_key);

        $this->schemaManager->deleteGraph('locations');
    }

    public function testGetGraphVertices()
    {
        if (! $this->schemaManager->hasCollection('characters')) {
            $this->schemaManager->createCollection('characters');
        }
        if (! $this->schemaManager->hasGraph('relations')) {
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
                true
            );
        }

        $results = $this->schemaManager->getGraphVertices('relations');

        $this->assertEquals(2, count($results));
        $this->assertEquals($results[0], 'characters');
        $this->assertEquals($results[1], 'orphanVertices');

        $this->schemaManager->deleteGraph('relations');
        $this->schemaManager->deleteCollection('children');
        $this->schemaManager->deleteCollection('characters');
        $this->schemaManager->deleteCollection('orphanVertices');
    }

    public function testAddGraphVertex()
    {
        if (! $this->schemaManager->hasGraph('relations')) {
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
                false
            );
        }
        $newVertex = 'houses';

        $result = $this->schemaManager->addGraphVertex('relations', $newVertex);

        $this->assertContains('orphanVertices', $result->orphanCollections);
        $this->assertContains($newVertex, $result->orphanCollections);

        $this->schemaManager->deleteGraph('relations');
        $this->schemaManager->deleteCollection('children');
        $this->schemaManager->deleteCollection('characters');
        $this->schemaManager->deleteCollection('orphanVertices');
        $this->schemaManager->deleteCollection($newVertex);
    }

    public function testRemoveGraphVertex()
    {
        if (! $this->schemaManager->hasGraph('relations')) {
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
                false
            );
        }

        $result = $this->schemaManager->removeGraphVertex('relations', 'orphanVertices', true);

        $this->assertNotContains('orphanVertices', $result->orphanCollections);

        $checkDropped = $this->schemaManager->hasCollection('orphanVertices');
        $this->assertFalse($checkDropped);

        $this->schemaManager->deleteGraph('relations');
        $this->schemaManager->deleteCollection('children');
        $this->schemaManager->deleteCollection('characters');
    }

    public function testGetGraphEdges()
    {
        if (! $this->schemaManager->hasCollection('characters')) {
            $this->schemaManager->createCollection('characters');
        }
        if (! $this->schemaManager->hasGraph('relations')) {
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
                true
            );
        }

        $results = $this->schemaManager->getGraphEdges('relations');

        $this->assertEquals(1, count($results));
        $this->assertEquals($results[0], 'children');

        $this->schemaManager->deleteGraph('relations');
        $this->schemaManager->deleteCollection('children');
        $this->schemaManager->deleteCollection('characters');
        $this->schemaManager->deleteCollection('orphanVertices');
    }

    public function testAddGraphEdge()
    {
        if (! $this->schemaManager->hasGraph('relations')) {
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
                false
            );
        }
        $newEdge = [
            'collection' => 'vassals',
            'from' => ['characters'],
            'to' => ['houses'],
        ];

        $result = $this->schemaManager->addGraphEdge('relations', $newEdge);

        $this->assertEquals($newEdge['collection'], $result->edgeDefinitions[1]->collection);

        $this->schemaManager->deleteGraph('relations');
        $this->schemaManager->deleteCollection('children');
        $this->schemaManager->deleteCollection('characters');
        $this->schemaManager->deleteCollection('vassals');
        $this->schemaManager->deleteCollection('houses');
    }

    public function testReplaceGraphEdge()
    {
        if (! $this->schemaManager->hasGraph('relations')) {
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
                ]
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
            true
        );

        $this->assertEquals($newEdge['collection'], $result->edgeDefinitions[0]->collection);

        $this->schemaManager->deleteGraph('relations');
        $this->schemaManager->deleteCollection('children');
        $this->schemaManager->deleteCollection('houses');
        $this->schemaManager->deleteCollection('characters');
    }

    public function testRemoveGraphEdge()
    {
        if (! $this->schemaManager->hasGraph('relations')) {
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
                ]
            );
        }

        $result = $this->schemaManager->removeGraphEdge(
            'relations',
            'children',
            true,
            true
        );

        $this->assertEquals(1, is_countable($result->edgeDefinitions) ? count($result->edgeDefinitions) : 0);
        $this->assertEquals('vassals', $result->edgeDefinitions[0]->collection);

        $this->schemaManager->deleteGraph('relations');
        $this->schemaManager->deleteCollection('children');
        $this->schemaManager->deleteCollection('houses');
        $this->schemaManager->deleteCollection('characters');
        $this->schemaManager->deleteCollection('vassals');
    }
}
