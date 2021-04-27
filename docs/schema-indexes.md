# Schema manager - Collections
You can use the schema manager to perform CRUD actions on indexes within a collection.

## Index  functions
The schema manager supports the following index functions:

### createIndex(string $collection, array $index): stdClass
Create an index on the given collection
```
$arangoClient->schema()->createIndex(
    'users',
     [
        'name' => 'email_persistent_unique',
        'type' => 'persistent',
        'fields' => ['profile.email'],
        'unique' => true,
        'sparse' => false
    ]
);
```

### deleteIndex(string $id): bool
Delete an index by its ID.
```
$arangoClient->schema()->deleteIndex($id);
```

### getIndex(string $id): stdClass
Get an index by its ID.
```
$arangoClient->schema()->getIndex($id);
```

### getIndexByName(string $collection, string $name): stdClass|false
Get an index by its name.
```
$arangoClient->schema()->getIndexByName('email_persistent_unique');
```

### getIndexes(string $collection): array
Get all indexes on a collection.
```
$arangoClient->schema()->getIndexes('users);
```