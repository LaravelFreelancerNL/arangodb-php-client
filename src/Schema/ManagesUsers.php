<?php

declare(strict_types=1);

namespace ArangoClient\Schema;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use stdClass;

/*
 * @see https://www.arangodb.com/docs/stable/http/views.html
 */
trait ManagesUsers
{
    protected ArangoClient $arangoClient;

    /**
     * @throws ArangoException
     */
    public function getUser(string $username): stdClass
    {
        $uri = '/_api/user/' . $username;

        return $this->arangoClient->request('get', $uri);
    }

    /**
     * @return array<mixed>
     *
     * @throws ArangoException
     */
    public function getUsers(): array
    {
        $results = $this->arangoClient->request('get', '/_api/user');

        return (array) $results->result;
    }

    /**
     * @throws ArangoException
     */
    public function hasUser(string $username): bool
    {
        $users = $this->getUsers();

        return in_array($username, array_column($users, 'user'), true);
    }

    /**
     * @param  array<mixed>  $user
     *
     * @throws ArangoException
     */
    public function createUser(array $user): stdClass
    {
        $options = [
            'body' => $user,
        ];

        return $this->arangoClient->request('post', '/_api/user', $options);
    }

    /**
     * @param  array<mixed>  $properties
     *
     * @throws ArangoException
     */
    public function updateUser(string $username, array $properties): stdClass
    {
        $uri = '/_api/user/' . $username;

        $options = ['body' => $properties];

        return $this->arangoClient->request('patch', $uri, $options);
    }

    /**
     * @param  array<mixed>  $user
     *
     * @throws ArangoException
     */
    public function replaceUser(string $username, array $user): stdClass
    {
        $uri = '/_api/user/' . $username;

        $options = ['body' => $user];

        return $this->arangoClient->request('put', $uri, $options);
    }

    /**
     * @throws ArangoException
     */
    public function deleteUser(string $username): bool
    {
        $uri = '/_api/user/' . $username;

        return (bool) $this->arangoClient->request('delete', $uri);
    }

    /**
     * @throws ArangoException
     */
    public function getDatabaseAccessLevel(string $username, string $database): string
    {
        $uri = '/_api/user/' . $username . '/database/' . $database;

        $results = $this->arangoClient->request('get', $uri);

        return (string) $results->result;
    }

    /**
     * @throws ArangoException
     */
    public function setDatabaseAccessLevel(string $username, string $database, string $grant): stdClass
    {
        $uri = '/_api/user/' . $username . '/database/' . $database;

        $options = [
            'body' => [
                'grant' => $grant,
            ],
        ];

        return $this->arangoClient->request('put', $uri, $options);
    }

    /**
     * @throws ArangoException
     */
    public function clearDatabaseAccessLevel(string $username, string $database): bool
    {
        $uri = '/_api/user/' . $username . '/database/' . $database;

        $this->arangoClient->request('delete', $uri);

        return true;
    }
}
