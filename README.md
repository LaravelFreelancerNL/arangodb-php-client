# ArangoClient - ArangoDB PHP client

Low level PHP client for ArangoDB. Supports PHP versions 7.4 & ^8.0

![Github CI tests](https://github.com/LaravelFreelancerNL/arangodb-php-client/workflows/CI%20tests/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/LaravelFreelancerNL/arangodb-php-client/badges/quality-score.png?b=1.x)](https://scrutinizer-ci.com/g/LaravelFreelancerNL/arangodb-php-client/?branch=1.x)
[![Code Coverage](https://scrutinizer-ci.com/g/LaravelFreelancerNL/arangodb-php-client/badges/coverage.png?b=1.x)](https://scrutinizer-ci.com/g/LaravelFreelancerNL/arangodb-php-client/?branch=1.x)
<a href="https://packagist.org/packages/laravel-freelancer-nl/arangodb-php-client"><img src="https://poser.pugx.org/laravel-freelancer-nl/arangodb-php-client/v/stable" alt="Latest Version"></a>
<a href="https://packagist.org/packages/laravel-freelancer-nl/arangodb-php-client"><img src="https://poser.pugx.org/laravel-freelancer-nl/arangodb-php-client/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel-freelancer-nl/arangodb-php-client"><img src="https://poser.pugx.org/laravel-freelancer-nl/arangodb-php-client/license" alt="License"></a>

## Install

```
composer require laravel-freelancer-nl/arangodb-php-client
```
## Quickstart

### Create a new client
``` 
$client = new ArangoClient($config);
``` 

### Create a collection
Use the schemaManager to create a new collection.
``` 
$client->schema()->createCollection('users');
``` 

### Get documents from the collection
``` 
$statement = $client->prepare('FOR user in Users RETURN user');
$statement->execute();
$users = $statement->fetchAll(); 
```
As there are no users yet in the above example this will yield an empty result.
Note that this client does not have any preconceptions about the data structure 
and thus everything is returned as raw arrays.

### config
The connector has a default configuration for a local ArangoDB instance at it's default port (8529).

## AQL statements
To run AQL queries you prepare a query, execute it and fetch the results. Much like PHP's PDO extension.

``` 
$statement = $client->prepare('FOR user in users RETURN user');
$statement->execute();
$users = $statement->fetchAll(); 
```

Alternatively you can traverse over the statement itself to get the results one at a time.
``` 
$statement = $client->prepare('FOR user in users RETURN user');
$statement->execute();
foreach ($statement as $document) {
    //
}
```

## Managers
You have access to several managers that allow you to perform specific tasks on your ArangoDB instance(s).
Their functions can be called on the manager.

### Admin manager
The admin manager manages administrative functions and information retrieval for the server/cluster.
``` 
$client->admin()->version();
```

### Schema manager
The schema manager manages all schema related operations.
``` 
$client->schema()->createDatabase('new_db');
```

### Transaction manager
The transaction manager takes care of all transactions. 
``` 
$client->transactions()->begin(['write' => ['users', 'teams']]);
```

## Documentation
1) [ArangoDB PHP client](docs/arangodb-client.md)
2) [AQL query statements](docs/statements.md)
3) [Admin manager](docs/admin-manager.md)
4) Schema manager
    1) [Database schema](docs/schema-databases.md)
    2) [User schema](docs/schema-users.md)
    3) [Collection schema](docs/schema-collections.md)
    4) [Index schema](docs/schema-indexes.md)
    5) [View schema](docs/schema-views.md)
    6) [Graph schema](docs/schema-graphs.md)
5) [Transaction manager](docs/transaction-manager.md)

## Related packages
* [AQL query builder](https://github.com/LaravelFreelancerNL/fluentaql)
* [ArangoDB Laravel Driver](https://github.com/LaravelFreelancerNL/laravel-arangodb)
