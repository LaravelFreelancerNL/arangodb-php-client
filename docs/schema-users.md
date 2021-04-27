# Schema manager - Users
You can use the schema manager to perform CRUD actions on database users.

## User functions
The schema manager supports the following index functions:

### getUser(string $username): array
Get user properties.
```
$arangoClient->schema()->getUser('kimiko');
```

### getUsers(): array
Get user properties of all visible users.
```
$arangoClient->schema()->getUsers();
```

### hasUser(string $username): bool
Check if a user exists.
```
$arangoClient->schema()->hasUser('kimiko');
```

### createUser(array $user): stdClass
Create a user.
```
$arangoClient->schema()->createUser([
    'user' => 'kimiko',
    'password' => 'highly secretive password'
]);
```

### updateUser(string $username, array $properties): stdClass
Update a user's properties
```
$arangoClient->schema()->updateUser([
    'user' => 'kimiko',
    ['active => false]
]);
```

### replaceUser(string $username, array $user): stdClass
Replace a user. Used to change the username.
```
$arangoClient->schema()->replaceUser([
    'user' => 'kimiko',
    [
        'user' => 'newUserName',
        'active' => true
    ]
]);
```

### deleteUser(string $username): bool
Delete a user.
```
$arangoClient->schema()->deleteUser('newUserName');
```

### getDatabaseAccessLevel(string $username, string $database): string
Get the access level a user has on a specific database.
```
$this->schemaManager->getDatabaseAccessLevel('root', '_system');
```

### setDatabaseAccessLevel(string $username, string $database, string $grant): stdClass
Set the access level a user has for a specific database.
```
$this->schemaManager->getDatabaseAccessLevel('kimiko', 'the_boys', 'rw');
```

### clearDatabaseAccessLevel(string $username, string $database): bool
Remove the access level of a user for a specific database.
```
$this->schemaManager->clearDatabaseAccessLevel('kimiko', 'the_boys');
```