<?php

declare(strict_types=1);

namespace ArangoClient\Monitor;

use ArangoClient\ArangoClient;
use ArangoClient\Manager;

class MonitorManager extends Manager
{
    public function __construct(protected ArangoClient $arangoClient) {}

    public function getMetrics(): \stdClass
    {
        $uri = '/_admin/metrics/v2';

        return $this->arangoClient->request('get', $uri);
    }
}
