<?php

declare(strict_types=1);

namespace ArangoClient\Admin;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Manager;
use stdClass;

class AdminManager extends Manager
{
    public function __construct(protected ArangoClient $arangoClient)
    {
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
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

        return (property_exists($result, 'transactions') && $result->transactions !== null) ? (array) $result->transactions : [];
    }
}
