# ArangoDB PHP Client
```
$arangoClient = new ArangoClient($config);
```

## Configuration
Upon creation, you can alter the default configuration of the client. The following options are available:
* endpoint = 'http://localhost:8529'
* host = null 
* port = null 
* username = null
* password = null
* database = '_system'
* connection = 'Keep-Alive'

```
$config = [
    'endpoint' => 'http://localhost:8529',
    'username' => 'your-database-username',
    'password' => 'your-database-password',
    'database'=> 'your-database'
];

$arangoClient = new ArangoClient($config);
```

### Support Guzzle configuration
In addition to the above mentioned options you can use the following Guzzle 7 specific options:
* allow_redirects
* connect_timeout

### Endpoint vs host/port
Some common packages and frameworks work with a host/port combination by default. 
When no endpoint is provided it is constructed from these two options.

```
$config = [
    'host' => 'http://localhost',
    'port' => '8529',
    'username' => 'your-database-username',
    'password' => 'your-database-password',
    'database'=> 'your-database'
];

$arangoClient = new ArangoClient($config);
```

## Functions

### request(string $method, string $uri, array $options = []): array
Send a request to ArangoDB's HTTP REST API. This is mostly for internal use but allows you to use unsupported endpoints.
```
$arangoClient->request(
    'get',
     '/_api/version', 
    'query' => [
        'details' => $details
    ]
]);
```

### getConfig(): array
Get the current configuration.
```
$arangoClient->getConfig()
```

### getUser(): string
Return the username;
```
$arangoClient->getUser();
```

### setDatabase(string $name): void
Set the database to be used for the upcoming requests
```
$arangoClient->setDatabase('ArangoClientDB');
```

### getDatabase(): string
Return the database name;
```
$database = $arangoClient->getDatabase();
```

### schema(): SchemaManager
Pass chained method to the schema manager.
```
$arangoClient->schema()->createCollection('users');
```

### admin(): AdminManager
Pass chained method to the admin manager.
```
$arangoClient->admin()->getVersion();
```