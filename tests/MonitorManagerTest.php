<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Prometheus\Prometheus;

class MonitorManagerTest extends TestCase
{
    public function testGetMetrics()
    {
        $result = $this->arangoClient->monitor()->getMetrics();
        $this->assertIsObject($result);
    }

    public function testSummaryMetric()
    {
        $prometheus = new Prometheus();

        $rawMetrics = '# HELP prometheus_rule_evaluation_duration_seconds The duration for a rule to execute.
# TYPE prometheus_rule_evaluation_duration_seconds summary
prometheus_rule_evaluation_duration_seconds{quantile="0.5"} 6.4853e-05
prometheus_rule_evaluation_duration_seconds{quantile="0.9"} 0.00010102
prometheus_rule_evaluation_duration_seconds{quantile="0.99"} 0.000177367
prometheus_rule_evaluation_duration_seconds_sum 1.623860968846092e+06
prometheus_rule_evaluation_duration_seconds_count 1.112293682e+09';


        $result = $prometheus->parseText($rawMetrics);
        ray($result);
        $this->assertIsObject($result);
    }

    public function testHistorgramParsing()
    {
        $prometheus = new Prometheus();

        $rawMetrics = '# HELP arangodb_aql_query_time Execution time histogram for all AQL queries [s]
# TYPE arangodb_aql_query_time histogram
arangodb_aql_query_time_bucket{role="SINGLE",le="0.000095"} 36 2211753600
arangodb_aql_query_time_bucket{role="SINGLE",le="0.000191"} 157 2211753601
arangodb_aql_query_time_bucket{role="SINGLE",le="0.000381"} 173 2211753602
arangodb_aql_query_time_bucket{role="SINGLE",le="0.000763"} 176 2211753603
arangodb_aql_query_time_bucket{role="SINGLE",le="0.001526"} 176 2211753604
arangodb_aql_query_time_bucket{role="SINGLE",le="0.003052"} 176 2211753605
arangodb_aql_query_time_bucket{role="SINGLE",le="0.006104"} 176 2211753606
arangodb_aql_query_time_bucket{role="SINGLE",le="0.012207"} 177 2211753607
arangodb_aql_query_time_bucket{role="SINGLE",le="0.024414"} 177 2211753608
arangodb_aql_query_time_bucket{role="SINGLE",le="0.048828"} 177 2211753609
arangodb_aql_query_time_bucket{role="SINGLE",le="0.097656"} 177 2211753610
arangodb_aql_query_time_bucket{role="SINGLE",le="0.195312"} 177 2211753611
arangodb_aql_query_time_bucket{role="SINGLE",le="0.390625"} 177 2211753612
arangodb_aql_query_time_bucket{role="SINGLE",le="0.781250"} 177 2211753613
arangodb_aql_query_time_bucket{role="SINGLE",le="1.562500"} 177 2211753614
arangodb_aql_query_time_bucket{role="SINGLE",le="3.125000"} 177 2211753615
arangodb_aql_query_time_bucket{role="SINGLE",le="6.250000"} 177 2211753616
arangodb_aql_query_time_bucket{role="SINGLE",le="12.500000"} 177 2211753617
arangodb_aql_query_time_bucket{role="SINGLE",le="25.000000"} 177 2211753618
arangodb_aql_query_time_bucket{role="SINGLE",le="+Inf"} 177
arangodb_aql_query_time_count{role="SINGLE"} 177
arangodb_aql_query_time_sum{role="SINGLE"} 0.035180
';

        $result = $prometheus->parseText($rawMetrics);
        ray($result);
        $this->assertIsObject($result);
    }

    public function testTimestampParsing()
    {
        $prometheus = new Prometheus();

        $rawMetrics = '# HELP arangodb_aql_local_query_memory_limit_reached_total Number of local AQL query memory limit violations
# TYPE arangodb_aql_local_query_memory_limit_reached_total counter
arangodb_aql_local_query_memory_limit_reached_total{role="SINGLE"} 0 2211753600';


        $result = $prometheus->parseText($rawMetrics);
        ray($result);
        $this->assertIsObject($result);
    }

}
