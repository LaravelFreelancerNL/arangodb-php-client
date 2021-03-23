# ArangoDB PHP client

Low level PHP client for ArangoDB. Supports PHP versions 7.4 & ^8.0

![Github CI tests](https://github.com/LaravelFreelancerNL/arangodb-php-client/workflows/CI%20tests/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/LaravelFreelancerNL/arangodb-php-client/badges/quality-score.png?b=next)](https://scrutinizer-ci.com/g/LaravelFreelancerNL/arangodb-php-client/?branch=next)
[![Code Coverage](https://scrutinizer-ci.com/g/LaravelFreelancerNL/arangodb-php-client/badges/coverage.png?b=next)](https://scrutinizer-ci.com/g/LaravelFreelancerNL/arangodb-php-client/?branch=next)
<a href="https://packagist.org/packages/laravel-freelancer-nl/arangodb-php-client"><img src="https://poser.pugx.org/laravel-freelancer-nl/arangodb-php-client/v/unstable" alt="Latest Version"></a>
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

## Managers
You have access to several managers that allow you to perform specific tasks on your ArangoDB instance(s).

(ArangoDB's endpoints are mapped to these managers in a semantically functional manner. Therefore a manager
may have functionality from different endpoints within ArangoDB's HTTP API.)

### Admin
Manages administrative functions to manage the server/cluster and retrieve server level information. 
``` 
$client->admin()->getVersion();
``` 

### Schema
The schema manager allows you perform tasks like creating databases, collections, indexes, views and graphs
``` 
$client->schema()->createCollection('users');
``` 

## Related packages
[AQL query builder](https://github.com/LaravelFreelancerNL/fluentaql)

[ArangoDB Laravel Driver](https://github.com/LaravelFreelancerNL/laravel-arangodb)