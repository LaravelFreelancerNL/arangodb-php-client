# Schema manager - Graphs
You can use the schema manager to perform CRUD actions on named graphs.

## Graph functions
The schema manager supports the following graph functions:

###  public function createGraph(string $name, array $config = [], $waitForSync = false)
```
$arangoClient->schema()->createGraph(
    'relations',
    [
        'edgeDefinitions' => [
            [
                'collection' => 'children',
                'from' => ['characters'],
                'to' => ['characters']
            ]
        ],
        'orphanCollections' => [
            'orphanVertices'
        ],
    ],
    true
);
```

###  getGraph(string $name): array
```
$arangoClient->schema()->getGraphs('relations');
```

###  getGraphs(): array
```
$arangoClient->schema()->getGraphs();
```

###  hasGraph(string $name): bool
```
$arangoClient->schema()->hasGraph('relations');
```

###  deleteGraph(string $name): bool
```
$arangoClient->schema()->deleteGraph('locations');
```

###  getGraphVertices(string $name): array
```
$arangoClient->schema()->getGraphVertices('relations');
```

###  addGraphVertex(string $name, string $vertex): array
```
$arangoClient->schema()->addGraphVertex('relations', 'houses');
```

###  removeGraphVertex(string $name, string $vertex, bool $dropCollection = false): array
```
$arangoClient->schema()->removeGraphVertex('relations', 'houses', true);
```

###  getGraphEdges(string $name): array
```
$arangoClient->schema()->deleteGraph('locations');
```

###  addGraphEdge(string $name, array $edgeDefinition): array
```
$arangoClient->schema()->addGraphEdge(
    'relations', 
     [
        'collection' => 'vassals',
        'from' => ['characters'],
        'to' => ['houses']
    ]
);
```

###  replaceGraphEdge(string $name, string $edge, array $edgeDefinition, bool $dropCollection = false, bool $waitForSync = false): array 
```
$arangoClient->schema()->createGraph(
    'relations',
    [
        'edgeDefinitions' => [
            [
                'collection' => 'children',
                'from' => ['characters'],
                'to' => ['characters']
            ]
        ]
    ]
);
```

###  removeGraphEdge(string $name, string $edge, bool $dropCollection = true, bool $waitForSync = false): array
```
$arangoClient->schema()->removeGraphEdge(
    'relations',
    'children',
    true,
    true
);
```

