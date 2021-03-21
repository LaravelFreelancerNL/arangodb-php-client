<?php

namespace ArangoClient\Schema;

use ArangoClient\Connector;
use ArangoClient\Exceptions\ArangoException;
use GuzzleHttp\Exception\GuzzleException;

/*
 * @see https://www.arangodb.com/docs/stable/http/database.html
 */
trait ManagesDatabases
{
    protected Connector $connector;

    /**
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function getCurrentDatabase(): array
    {
        return (array) $this->connector->request('get', '/_api/database/current');
    }

    /**
     * @return array<mixed>
     *
     * @throws GuzzleException|ArangoException
     */
    public function listDatabases(): array
    {
        return (array) $this->connector->request('get', '/_api/database');
    }

    /**
     * @param string    $database
     * @return bool
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function hasDatabase(string $database): bool
    {
        $databaseList = $this->listDatabases();
        return in_array($database, $databaseList);
    }

    /**
     * @return array<mixed>
     *
     * @throws GuzzleException|ArangoException
     */
    public function listMyDatabases(): array
    {
        return (array) $this->connector->request('get', '/_api/database/user');
    }

    /**
     * @param  string  $name
     * @param  null  $options
     * @param  null  $users
     * @return bool
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function createDatabase(string $name, $options = null, $users = null): bool
    {
        $database = json_encode((object)['name' => $name, 'options' => $options, 'users' => $users]);

        return (bool) $this->connector->request('post', '/_api/database', ['body' => $database]);
    }

    /**
     * @param  string  $name
     * @return bool
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function deleteDatabase(string $name): bool
    {
        $uri = '/_api/database/' . $name;

        return (bool) $this->connector->request('delete', $uri);
    }
}
