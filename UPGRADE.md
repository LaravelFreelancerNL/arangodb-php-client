# Upgrade Guide

## Upgrading From 1.x To  2.x

### Response objects instead of associative arrays
The biggest change is the decoding of responses. JSON objects are now decoded to POPO's (Plain Old Php Objects).
This will influence how you use the response. Together with the improvements in php 8 this makes the result data much
nicer to work with compared to associative arrays.

This client is just a conduit, so it doesn't make any presumptions on the returned data itself, hence the the objects 
are of the stdClass type.

### PHP 8
PHP 8.0 is now the minimum supported version. You will need to run your app with this version
and ensure that other packages support it.

If you need to use PHP 7.4 you can use the maintenance branch 1.x

### Schema function result data
Some schema functions just returned a boolean to indicate success or failure. These now match
the returned result by ArangoDB.