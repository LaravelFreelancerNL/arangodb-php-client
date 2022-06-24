<?php

declare(strict_types=1);

namespace ArangoClient\Admin;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Manager;
use stdClass;

class AdminManager extends Manager
{
    protected ArangoClient $arangoClient;

    public function __construct(ArangoClient $arangoClient)
    {
        $this->arangoClient = $arangoClient;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param  bool  $details
     * @return stdClass
     *
     * @throws ArangoException
     */
    public function getVersion(bool $details = false): stdClass
    {
        return $this->arangoClient->request(
            'get',
            '/_api/version',
            [
                'query' => [
                    'details' => $details,
                ],
            ]
        );
    }

    /**
     * @return array<mixed>
     *
     * @throws ArangoException
     */
    public function getRunningTransactions(): array
    {
        $result = $this->arangoClient->request('get', '/_api/transaction');

        return (isset($result->transactions)) ? (array) $result->transactions : [];
    }
}
