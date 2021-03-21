<?php

declare(strict_types=1);

namespace ArangoClient\Administration;

use ArangoClient\Connector;
use ArangoClient\Exceptions\ArangoException;
use GuzzleHttp\Exception\GuzzleException;

class AdministrationClient
{
    /**
     * @var Connector
     */
    protected Connector $connector;

    /**
     * Documents constructor.
     * @param  Connector  $connector
     */
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param  bool  $details
     * @return array<mixed>
     *
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function version(bool $details = false): array
    {
        return (array) $this->connector->request(
            'get',
            '/_api/version',
            [
                'query' => [
                    'details' => $details
                ]
            ]
        );
    }
}
