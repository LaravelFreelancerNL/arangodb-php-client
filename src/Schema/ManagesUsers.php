<?php

namespace ArangoClient\Schema;

use ArangoClient\Connector;
use ArangoClient\Exceptions\ArangoException;
use GuzzleHttp\Exception\GuzzleException;

/*
 * @see https://www.arangodb.com/docs/stable/http/views.html
 */
trait ManagesUsers
{
    protected Connector $connector;

    /**
     * @param  string  $username
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function getUser(string $username): array
    {
        $uri = '/_api/user/' . $username;

        return (array) $this->connector->request('get', $uri);
    }

    /**
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function getUsers(): array
    {
        return (array) $this->connector->request('get', '/_api/user');
    }

    /**
     * @param  string  $username
     * @return bool
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function hasUser(string $username): bool
    {
        $users = $this->getUsers();

        return array_search($username, array_column($users, 'user'), true) !== false;
    }

    /**
     * @param  array<mixed>  $user
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function createUser(array $user): array
    {
        $body = json_encode((object) $user);

        return (array) $this->connector->request('post', '/_api/user', ['body' => $body]);
    }

    /**
     * @param  string  $username
     * @param array<mixed> $properties
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function updateUser(string $username, array $properties): array
    {
        $uri = '/_api/user/' . $username;

        $properties = json_encode((object) $properties);
        $options = ['body' => $properties];

        return (array) $this->connector->request('patch', $uri, $options);
    }

    /**
     * @param  string  $username
     * @param  array<mixed>  $user
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function replaceUser(string $username, array $user): array
    {
        $uri = '/_api/user/' . $username;

        $user = json_encode((object) $user);
        $options = ['body' => $user];

        return (array) $this->connector->request('put', $uri, $options);
    }

    /**
     * @param  string  $username
     * @return bool
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function deleteUser(string $username): bool
    {
        $uri = '/_api/user/' . $username;

        return (bool) $this->connector->request('delete', $uri);
    }

    /**
     * @param  string  $username
     * @param  string  $database
     * @return string
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function getDatabaseAccessLevel(string $username, string $database): string
    {
        $uri = '/_api/user/' . $username . '/database/' . $database;

        return (string) $this->connector->request('get', $uri);
    }

    /**
     * @param  string  $username
     * @param  string  $database
     * @param  string  $grant
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function setDatabaseAccessLevel(string $username, string $database, string $grant): array
    {
        $uri = '/_api/user/' . $username . '/database/' . $database;

        $grant = json_encode((object) ['grant' => $grant]);
        $options = ['body' => $grant];

        return (array) $this->connector->request('put', $uri, $options);
    }

    /**
     * @param  string  $username
     * @param  string  $database
     * @return bool
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function clearDatabaseAccessLevel(string $username, string $database)
    {
        $uri = '/_api/user/' . $username . '/database/' . $database;

        $result = (array) $this->connector->request('delete', $uri);

        return ((int) $result['code'] === 202);
    }
}
