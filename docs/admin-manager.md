# Admin manager

Manages administrative functions to manage the server/cluster and retrieve server level information.

## Functions
The admin manager supports the following functions:

### getVersion(bool $details = false): array
Get the ArangoDB version of the server

```
$arangoClient->admin()->getVersion();
```