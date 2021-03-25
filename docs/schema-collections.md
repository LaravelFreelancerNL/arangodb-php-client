# Schema manager - Collections
You can use the schema manager to perform CRUD actions on collections within an ArangoDB database.

## Collection functions
The schema manager supports the following collection functions:

### getCollections(bool $excludeSystemCollections = false): array
Get a list of collections within the database
```
$arangoClient->schema()->getCollections();
```

### getCollection(string $name): array
Get the requested collection.
```
$arangoClient->schema()->getCollection('_fishbowl');
```

### getCollectionProperties(string $collection): array
Get the properties of the requested collection.
```
$arangoClient->schema()->getCollectionProperties('_fishbowl');
```

### getCollectionWithDocumentCount(string $collection): array
Get the properties of the requested collection.
```
$arangoClient->schema()->getCollectionWithDocumentCount('_fishbowl');
```

### getCollectionStatistics(string $collection, bool $details = false): array
Get the properties of the requested collection.
```
$arangoClient->schema()->getCollectionStatistics('_fishbowl');
```

### hasCollection(string $collection): bool
Check if a collection exists.
```
$arangoClient->schema()->hasCollection('_fishbowl');
```

### createCollection(string $collection, array $config = [], $waitForSyncReplication = null, $enforceReplicationFactor = null): bool
Create a collection
```
$arangoClient->schema()->createCollection('users');
```

### updateCollection(string $name, array $config = []): array
Update a collection
```
$arangoClient->schema()->updateCollection('users', ['waitForSync' => true]);
```

### renameCollection(string $old, string $new): array
Rename a collection
```
$arangoClient->schema()->renameCollection('users', 'characters');
```

### deleteCollection(string $name): bool
Delete a collection
```
$arangoClient->schema()->deleteCollection('users');
```

