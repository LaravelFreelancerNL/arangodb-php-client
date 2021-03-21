<?php

declare(strict_types=1);

namespace ArangoClient;

use GuzzleHttp\Exception\GuzzleException;

class DatabaseClient
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
     * @return array<mixed>
     * @throws GuzzleException|Exceptions\ArangoDbException
     */
    public function read(): array
    {
        return (array) $this->connector->request('get', '/_api/database/current');
    }

    /**
     * @return array<mixed>
     *
     * @throws GuzzleException|Exceptions\ArangoDbException
     */
    public function listDatabases(): array
    {
        return (array) $this->connector->request('get', '/_api/database');
    }

    /**
     * @return array<mixed>
     *
     * @throws GuzzleException|Exceptions\ArangoDbException
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
     * @throws Exceptions\ArangoDbException
     * @throws GuzzleException
     */
    public function create(string $name, $options = null, $users = null): bool
    {
        $database = json_encode((object)['name' => $name, 'options' => $options, 'users' => $users]);

        return (bool) $this->connector->request('post', '/_api/database', ['body' => $database]);
    }

    /**
     * @param  string  $name
     * @return bool
     * @throws Exceptions\ArangoDbException
     * @throws GuzzleException
     */
    public function delete(string $name): bool
    {
        $uri = '/_api/database/' . $name;

        return (bool) $this->connector->request('delete', $uri);
    }
}
