# ArangoDB PHP client

Low level PHP client for ArangoDB. Supports PHP versions 7 & 8.

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

``` 
// Create a new connector        
$this->connector = new Connector($config);

//Create the domain clients that suit your purpose and pass the connector to the client.
$this->schemaClient = new SchemaClient($this->connector);

//Create a database
$this->schemaClient->createDatabase('MyKillahProject');
```

### config
The connector has a default configuration for a local ArangoDB instance at it's default port (8529).

## Client domains
### AdministrationClient
Manages administrative functions


### SchemaClient
Manages schema related tasks like creating databases, collections, indexes, views and graphs

## Related packages
[AQL query builder](https://github.com/LaravelFreelancerNL/fluentaql)

[ArangoDB Laravel Driver](https://github.com/LaravelFreelancerNL/laravel-arangodb)