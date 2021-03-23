<?php

declare(strict_types=1);

namespace ArangoClient\Admin;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Manager;

class AdminManager extends Manager
{
    /**
     * @var ArangoClient
     */
    protected ArangoClient $arangoClient;

    /**
     * Documents constructor.
     * @param  ArangoClient  $arangoClient
     */
    public function __construct(ArangoClient $arangoClient)
    {
        $this->arangoClient = $arangoClient;
    }

    /**
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param  bool  $details
     * @return array<mixed>
     *
     * @throws ArangoException
     */
    public function getVersion(bool $details = false): array
    {
        return $this->arangoClient->request(
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
