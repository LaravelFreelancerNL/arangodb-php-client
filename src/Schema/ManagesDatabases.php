<?php

namespace ArangoClient\Schema;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;

/*
 * @see https://www.arangodb.com/docs/stable/http/database.html
 */
trait ManagesDatabases
{
    protected ArangoClient $arangoClient;

    /**
     * @return array<mixed>
     * @throws ArangoException
     */
    public function getCurrentDatabase(): array
    {
        $results = $this->arangoClient->request('get', '/_api/database/current');

        return (array) $results['result'];
    }

    /**
     * @param  string|null  $database
     * @return array<mixed>
     *
     * @throws ArangoException
     */
    public function getDatabases(?string $database = null): array
    {
        $user = $this->arangoClient->getUser();

        $uri = '/_api/user/' . $user . '/database';

        $results = $this->arangoClient->request('get', $uri, [], $database);

        return array_keys((array) $results['result']);
    }

    /**
     * @param string    $name
     * @return bool
     * @throws ArangoException
     */
    public function hasDatabase(string $name): bool
    {
        $databaseList = $this->getDatabases();

        return in_array($name, $databaseList);
    }

    /**
     * @param  string  $name
     * @param  null  $options
     * @param  null  $users
     * @return bool
     * @throws ArangoException
     */
    public function createDatabase(string $name, $options = null, $users = null): bool
    {
        $database = json_encode((object)['name' => $name, 'options' => $options, 'users' => $users]);

        return (bool) $this->arangoClient->request('post', '/_api/database', ['body' => $database], '_system');
    }

    /**
     * @param  string  $name
     * @return bool
     * @throws ArangoException
     */
    public function deleteDatabase(string $name): bool
    {
        $uri = '/_api/database/' . $name;

        return (bool) $this->arangoClient->request('delete', $uri, [], '_system');
    }
}
