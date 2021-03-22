<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Exceptions\ArangoException;
use GuzzleHttp\Exception\GuzzleException;


class SchemaClientUsersTest extends TestCase
{
    protected string $userName = 'kimiko';
    protected string $accessDatabase = 'arangodb_php_client_access__test';

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $user = [
            'user' => $this->userName,
            'password' => 'yee random hashed pw'
        ];

        if (! $this->schemaClient->hasUser($this->userName)) {
            $this->schemaClient->createUser($user);
        }
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->schemaClient->hasUser($this->userName)) {
            $this->schemaClient->deleteUser($this->userName);
        }
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetUser()
    {
        $name = 'root';
        $user = $this->schemaClient->getUser($name);
        $this->assertIsArray($user);
        $this->assertSame($name, $user['user']);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetUsers()
    {
        $users = $this->schemaClient->getUsers();
        $this->assertIsArray($users);
        $this->assertArrayHasKey('user', $users[0]);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testHasUser()
    {
        $result = $this->schemaClient->hasUser('root');
        $this->assertTrue($result);

        $result = $this->schemaClient->hasUser('nonExistingUser');
        $this->assertFalse($result);

    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testCreateAndDeleteUser()
    {
        $user = [
            'user' => 'admin',
            'passwd' => 'highly secretive hashed password',
            'active' => true,
            'extra' => [
                'profile' => [
                    'name' => 'Billy Butcher'
                ]
            ]
        ];
        $created = $this->schemaClient->createUser($user);
        $this->assertSame($user['user'], $created['user']);

        $this->schemaClient->deleteUser($user['user']);
    }

    public function testUpdateUser()
    {
        $newUserData = [
            'user' => $this->userName,
            'active' => false
        ];
        $updated = $this->schemaClient->updateUser($this->userName, $newUserData);

        $this->assertSame($newUserData['user'], $updated['user']);
    }

    public function testReplaceUser()
    {
        $newUserData = [
            'user' => 'newUserName',
            'active' => false
        ];
        $replaced = $this->schemaClient->replaceUser($this->userName, $newUserData);

        $this->assertSame($this->userName, $replaced['user']);
    }

    public function testGetDatabaseAccessLevel()
    {
        $accessLevel = $this->schemaClient->getDatabaseAccessLevel('root', '_system');

        $this->assertSame('rw', $accessLevel);
    }

    public function testSetDatabaseAccessLevel()
    {
        $this->setUpAccessTest();
        $grant = 'rw';

        $results = $this->schemaClient->setDatabaseAccessLevel($this->userName, $this->accessDatabase, $grant);
        $accessLevel = $this->schemaClient->getDatabaseAccessLevel($this->userName, $this->accessDatabase);

        $this->assertArrayHasKey( $this->accessDatabase, $results);
        $this->assertSame($grant, $results[ $this->accessDatabase]);
        $this->assertSame($grant, $accessLevel);

        $this->tearDownAccessTest();
    }

    public function testClearDatabaseAccessLevel()
    {
        $this->setUpAccessTest();
        $grant = 'rw';

        $this->schemaClient->setDatabaseAccessLevel($this->userName, $this->accessDatabase, $grant);
        $accessLevel = $this->schemaClient->getDatabaseAccessLevel($this->userName, $this->accessDatabase);
        $this->assertSame($grant, $accessLevel);

        $result = $this->schemaClient->clearDatabaseAccessLevel($this->userName, $this->accessDatabase);
        $accessLevel = $this->schemaClient->getDatabaseAccessLevel($this->userName, $this->accessDatabase);

        $this->assertTrue($result);
        $this->assertSame('none', $accessLevel);

        $this->tearDownAccessTest();
    }

    protected function setUpAccessTest()
    {
        $this->schemaClient->createDatabase($this->accessDatabase);
    }

    protected function tearDownAccessTest()
    {
        $this->schemaClient->deleteDatabase($this->accessDatabase);
    }
}