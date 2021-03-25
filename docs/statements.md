# AQL Statements
To run AQL queries you prepare a query, execute it and fetch the results. Much like PHP's PDO extension.
``` 
$statement = $client->prepare('FOR user in users RETURN user');
$statement->execute();
$users = $statement->fetchAll(); 

foreach ($users as $user) {
    //
}
```

Alternatively you can traverse over the statement itself to get the results one at a time.
``` 
$statement = $client->prepare('FOR user in users RETURN user');
$statement->execute();
foreach ($statement as $document) {
    //
}
```

## Statement functions

### explain(): array
Get information on the current database
```
$statement->explain();
```

### parse(): array
Parses the query and throws an ArangoException if the AQL is invalid.
```
$statement->parse();
```

### profile(): array
Execute the query and return the results and query performance information. 
```
$statement->profile();
```

### execute(): bool
Execute the query. The results are loaded in the statement which can be iterated over. 
```
$statement->execute();
```

### fetchAll(): array
Execute the query. The results are loaded in the statement which can be iterated over. 
```
$statement->execute();
```

### setQuery(string $query): self
Execute the query. The results are loaded in the statement which can be iterated over.
```
$statement->setQuery('FOR user IN users RETURN user');
```

### getQuery(): string
Execute the query. The results are loaded in the statement which can be iterated over.
```
$query = $statement->getQuery();
```

### getCount(): ?int
Get the total number of results from the database. Only available if the corresponding query option is set. 
```
$statement->getCount();
```

