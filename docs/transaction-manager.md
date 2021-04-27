# Transaction manager
The transaction manager keeps track of running transactions. It can begin new transactions and abort or commit existing 
ones. Multiple transactions may be running in parallel. 

Methods that can use a transaction will use the most recent transaction by default.
In addition, you can send requests to the server for a specific transaction using the transActionAwareRequest
method on the client.


## Client functions
The transaction manager supports the following functions:

### begin(array $collections = [], array $options = []): string
Begin a transaction. collects that are written too much be supplied in the first argument by their mode. E.g:

```
$transactionId = $arangoClient->begin([
    'read' => ['roles'],
    'write' => ['users', 'teams]
])
```

### abort(string $id = null): bool
Abort the most recent transaction. No commands in the transaction are persisted. 
Optionally you may supply a transaction id to abort a specific transaction. 

```
$arangoClient->abort()
```

### commit(string $id = null): bool
Commit the most recent transaction. The commands are persisted. 
Optionally you may supply a transaction id to commit a specific transaction. 

```
$arangoClient->abort()
```

### transactionAwareRequest(string $method, string $uri, array $options = [], ?string $database = null, ?int $transactionId = null): stdClass
To send a request to the server that has to use a transaction you can use this request wrapper method.
By default, the latest transaction will be used, or you can supply a specific transaction id.

```
$arangoClient->transactionAwareRequest('post', '/_api/cursor', ['body' => $body]);
```

## Manager functions

### getTransaction(string $id = null)
Get the latest transaction id, or validate a given id. This method will throw if the id isn't available.

```
$arangoClient->transactions()->getTransaction('123');
```

### getTransaction()
Get a list of all running transactions for this TransactionManager object.

```
$arangoClient->transactions()->getTransactions();
```


## Coming from PHP's PDO extension
The client has three quality of life shortcut methods named after PDO. (beginTransaction, rollBack & commit).
These are meant to make integration with existing packages easier.<br> 
_However unlike PDO_, you must provide the collections to which you will write in either write or exclusive mode.

### beginTransaction(array $collections = [], array $options = []): string
This is a shortcut to begin()

### rollBack(string $id = null): bool
This is a shortcut to abort()
