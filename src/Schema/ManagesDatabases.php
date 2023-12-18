<?php

namespace ArangoClient\Schema;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use stdClass;

/*
 * @see https://www.arangodb.com/docs/stable/http/database.html
 */
trait ManagesDatabases
{
    protected ArangoClient $arangoClient;

    /**
     * @throws ArangoException
     */
    public function getCurrentDatabase(): stdClass
    {
        $results = $this->arangoClient->request('get', '/_api/database/current');

        return (object) $results->result;
    }

    /**
     * @return array<mixed>
     *
     * @throws ArangoException
     */
    public function getDatabases(): array
    {
        $user = $this->arangoClient->getUser();

        $uri = '/_api/user/' . $user . '/database';

        $results = $this->arangoClient->request('get', $uri, []);

        return array_keys((array) $results->result);
    }

    /**
     * @throws ArangoException
     */
    public function hasDatabase(string $name): bool
    {
        $databaseList = $this->getDatabases();

        return in_array($name, $databaseList);
    }

    /**
     * @param  null  $options
     * @param  null  $users
     *
     * @throws ArangoException
     */
    public function createDatabase(string $name, $options = null, $users = null): bool
    {
        $body = ['name' => $name, 'options' => $options, 'users' => $users];

        $options = [
            'body' => $body,
        ];

        return (bool) $this->arangoClient->request('post', '/_api/database', $options, '_system');
    }

    /**
     * @throws ArangoException
     */
    public function deleteDatabase(string $name): bool
    {
        $uri = '/_api/database/' . $name;

        return (bool) $this->arangoClient->request('delete', $uri, [], '_system');
    }
}
