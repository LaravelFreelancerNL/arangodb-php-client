# Monitor manager

Manages monitoring functions for the server/cluster.

## Functions
The monitor manager supports the following functions:

### getMetrics(): Metrics
Get Prometheus metrics of the server

```
$arangoClient->monitor()->getMetrics();
```