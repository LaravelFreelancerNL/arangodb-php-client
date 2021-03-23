<?php

declare(strict_types=1);

namespace ArangoClient\Schema;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;

/*
 * @see https://www.arangodb.com/docs/stable/http/views.html
 */
trait ManagesUsers
{
    protected ArangoClient $arangoClient;

    /**
     * @param  string  $username
     * @return array<mixed>
     * @throws ArangoException
     */
    public function getUser(string $username): array
    {
        $uri = '/_api/user/' . $username;

        $result = $this->arangoClient->request('get', $uri);

        return $this->sanitizeRequestMetadata($result);
    }

    /**
     * @return array<mixed>
     * @throws ArangoException
     */
    public function getUsers(): array
    {
        $results = $this->arangoClient->request('get', '/_api/user');

        return (array) $results['result'];
    }

    /**
     * @param  string  $username
     * @return bool
     * @throws ArangoException
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
     */
    public function createUser(array $user): array
    {
        $body = json_encode((object) $user);

        $result = $this->arangoClient->request('post', '/_api/user', ['body' => $body]);

        return $this->sanitizeRequestMetadata($result);
    }

    /**
     * @param  string  $username
     * @param array<mixed> $properties
     * @return array<mixed>
     * @throws ArangoException
     */
    public function updateUser(string $username, array $properties): array
    {
        $uri = '/_api/user/' . $username;

        $properties = json_encode((object) $properties);
        $options = ['body' => $properties];

        $result = $this->arangoClient->request('patch', $uri, $options);

        return $this->sanitizeRequestMetadata($result);
    }

    /**
     * @param  string  $username
     * @param  array<mixed>  $user
     * @return array<mixed>
     * @throws ArangoException
     */
    public function replaceUser(string $username, array $user): array
    {
        $uri = '/_api/user/' . $username;

        $user = json_encode((object) $user);
        $options = ['body' => $user];

        $result = $this->arangoClient->request('put', $uri, $options);

        return $this->sanitizeRequestMetadata($result);
    }

    /**
     * @param  string  $username
     * @return bool
     * @throws ArangoException
     */
    public function deleteUser(string $username): bool
    {
        $uri = '/_api/user/' . $username;

        return (bool) $this->arangoClient->request('delete', $uri);
    }

    /**
     * @param  string  $username
     * @param  string  $database
     * @return string
     * @throws ArangoException
     */
    public function getDatabaseAccessLevel(string $username, string $database): string
    {
        $uri = '/_api/user/' . $username . '/database/' . $database;

        $results = $this->arangoClient->request('get', $uri);

        return (string) $results['result'];
    }

    /**
     * @param  string  $username
     * @param  string  $database
     * @param  string  $grant
     * @return array<mixed>
     * @throws ArangoException
     */
    public function setDatabaseAccessLevel(string $username, string $database, string $grant): array
    {
        $uri = '/_api/user/' . $username . '/database/' . $database;

        $grant = json_encode((object) ['grant' => $grant]);
        $options = ['body' => $grant];

        $result = $this->arangoClient->request('put', $uri, $options);

        return $this->sanitizeRequestMetadata($result);
    }

    /**
     * @param  string  $username
     * @param  string  $database
     * @return bool
     * @throws ArangoException
     */
    public function clearDatabaseAccessLevel(string $username, string $database): bool
    {
        $uri = '/_api/user/' . $username . '/database/' . $database;

        $result = $this->arangoClient->request('delete', $uri);

        return ((int) $result['code'] === 202);
    }
}
