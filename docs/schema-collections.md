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

### getCollectionProperties(string $name): array
Get the properties of the requested collection.
```
$arangoClient->schema()->getCollectionProperties('_fishbowl');
```

### getCollectionWithDocumentCount(string $name): array
Get the properties of the requested collection.
```
$arangoClient->schema()->getCollectionWithDocumentCount('_fishbowl');
```

### getCollectionDocumentCount(string $name): int
Get the number of documents within the requested collection.
```
$arangoClient->schema()->getCollectionDocumentCount('users');
```

### getCollectionStatistics(string $name, bool $details = false): array
Get the properties of the requested collection.
```
$arangoClient->schema()->getCollectionStatistics('_fishbowl');
```

### hasCollection(string $name): bool
Check if a collection exists.
```
$arangoClient->schema()->hasCollection('_fishbowl');
```

### createCollection(string $name, array $config = [], $waitForSyncReplication = null, $enforceReplicationFactor = null): bool
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

### truncateCollection(string $name): bool
Truncate a collection.
```
$arangoClient->schema()->truncateCollection('teams');
```

### deleteCollection(string $name): bool
Delete a collection
```
$arangoClient->schema()->deleteCollection('users');
```

