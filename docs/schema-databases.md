# Schema manager - Databases
You can use the schema manager to perform CRUD actions on databases within ArangoDB. 

## Database functions

### getCurrentDatabase(): stdClass
Get information on the current database
```
$arangoClient->schema()->getCurrentDatabase();
```

### getDatabases(): array
Get a list of databases accessible to the current user.
```
$arangoClient->schema()->getDatabases();
```

### hasDatabase(string $name): bool
Check if a database exists
```
$arangoClient->schema()->hasDatabase('someNoneExistingDatabase');
```

### createDatabase(string $name, $options = null, $users = null): bool
Create a database.

[See ArangoDB's documentation for the current available options](https://www.arangodb.com/docs/stable/http/database-database-management.html#create-database)
```
$arangoClient->schema()->createDatabase('someNoneExistingDatabase');
```

### deleteDatabase(string $name): bool
Delete the given database.
```
$arangoClient->schema()->deleteDatabase('someExistingDatabase');
```
