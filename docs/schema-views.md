# Schema manager - Views
You can use the schema manager to perform CRUD actions on ArangoSearch views.

## View functions
The schema manager supports the following index functions:

###  createView(array $view): stdClass
```
$arangoClient->schema()->createView([
        'name' => 'testViewBasics',
        'type' => 'arangosearch'
    ]);
```

###  getView(string $name): stdClass
```
$arangoClient->schema()->getView('testViewBasics');
```

###  getViews(): array
```
$arangoClient->schema()->getViews();
```

###  hasView(string $name): bool
```
$arangoClient->schema()->hasView('testViewBasics');
```


###  getViewProperties(string $name): stdClass
```
$arangoClient->schema()->getViewProperties('testViewBasics');
```

###  renameView(string $old, string $new): stdClass
```
$arangoClient->schema()->renameView('testViewBasics', 'pages');
```

###  updateView(string $name, array $properties): stdClass
```
$arangoClient->schema()->updateView('pages', [
        'cleanupIntervalStep' => 3
    ]);
```

###  replaceView(string $name, array $newView): stdClass|false
Use replaceView if you want to update the primarySort or primarySortCompression.
This will delete the old view and create a new one. The new view will need to be build from the data so might
not be available right away.
```
$arangoClient->schema()->updateView('pages', [
        'cleanupIntervalStep' => 3,
        'primarySort' => [[
            'field' => 'email',
            'direction' => 'desc'
        ]]
    ]);
```

###  deleteView(string $name): bool
```
$arangoClient->schema()->deleteView('testViewBasics');
```

