# Admin manager

Manages administrative functions and information retrieval for the server/cluster.

## Functions
The admin manager supports the following functions:

### getVersion(bool $details = false): array
Get the ArangoDB version of the server

```
$arangoClient->admin()->getVersion();
```