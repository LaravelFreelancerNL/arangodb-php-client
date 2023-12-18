# Schema manager - Analyzers
You can use the schema manager to perform CRUD actions on ArangoDB analyzers.

## Analyzer functions
The schema manager supports the following analyzer functions:

###  createAnalyzer(array $analyzer): stdClass
```
$arangoClient->schema()->createAnalyzer([
        'name' => 'myAnalyzer',
        'type' => 'identity',
    ]);
```

###  getAnalyzer(string $name): stdClass
```
$arangoClient->schema()->getAnalyzer('myAnalyzer');
```

###  getAnalyzers(): array
```
$arangoClient->schema()->getAnalyzers();
```

###  hasAnalyzer(string $name): bool
```
$arangoClient->schema()->hasAnalyzer('myAnalyzer');
```

###  replaceAnalyzer(string $name, array $newAnalyzer): stdClass|false
This will delete the old analyzer and create a new one under the same name. 
```
$arangoClient->schema()->replaceAnalyzer('myAnalyzer', [
        'name' => 'myAnalyzer',
        'type' => 'identity',
    ]);
```

###  deleteAnalyzer(string $name): bool
```
$arangoClient->schema()->deleteAnalyzer('myAnalyzer');
```

