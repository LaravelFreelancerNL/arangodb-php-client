<?php

declare(strict_types=1);

namespace Tests;

class SchemaManagerUsersTest extends TestCase
{
    protected string $userName = 'kimiko';

    protected string $accessDatabase = 'arangodb_php_client_access__test';

    protected function setUp(): void
    {
        parent::setUp();

        $user = [
            'user' => $this->userName,
            'password' => 'yee random pw',
        ];

        if (! $this->schemaManager->hasUser($this->userName)) {
            $this->schemaManager->createUser($user);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->schemaManager->hasUser($this->userName)) {
            $this->schemaManager->deleteUser($this->userName);
        }
    }

    public function testGetUser()
    {
        $name = 'root';
        $user = $this->schemaManager->getUser($name);

        $this->assertSame($name, $user->user);
    }

    public function testGetUsers()
    {
        $users = $this->schemaManager->getUsers();
        $this->assertIsArray($users);
        $this->assertObjectHasAttribute('user', $users[0]);
    }

    public function testHasUser()
    {
        $result = $this->schemaManager->hasUser('root');
        $this->assertTrue($result);

        $result = $this->schemaManager->hasUser('nonExistingUser');
        $this->assertFalse($result);
    }

    public function testCreateAndDeleteUser()
    {
        $user = [
            'user' => 'admin',
            'passwd' => 'highly secretive password',
            'active' => true,
            'extra' => [
                'profile' => [
                    'name' => 'Billy Butcher',
                ],
            ],
        ];
        if ($this->schemaManager->hasUser($user['user'])) {
            $this->schemaManager->deleteUser($user['user']);
        }

        $created = $this->schemaManager->createUser($user);
        $this->assertSame($user['user'], $created->user);

        $this->schemaManager->deleteUser($user['user']);
        $checkDeleted = $this->schemaManager->hasUser($user['user']);
        $this->assertFalse($checkDeleted);
    }

    public function testUpdateUser()
    {
        $newUserData = [
            'user' => $this->userName,
            'active' => false,
        ];
        $updated = $this->schemaManager->updateUser($this->userName, $newUserData);

        $this->assertSame($newUserData['user'], $updated->user);
    }

    public function testReplaceUser()
    {
        $newUserData = [
            'user' => 'newUserName',
            'active' => false,
        ];
        $replaced = $this->schemaManager->replaceUser($this->userName, $newUserData);

        $this->assertSame($this->userName, $replaced->user);
    }

    public function testGetDatabaseAccessLevel()
    {
        $accessLevel = $this->schemaManager->getDatabaseAccessLevel('root', '_system');

        $this->assertSame('rw', $accessLevel);
    }

    public function testSetDatabaseAccessLevel()
    {
        $this->setUpAccessTest();
        $grant = 'rw';

        $results = $this->schemaManager->setDatabaseAccessLevel($this->userName, $this->accessDatabase, $grant);
        $accessLevel = $this->schemaManager->getDatabaseAccessLevel($this->userName, $this->accessDatabase);

        $this->assertObjectHasAttribute($this->accessDatabase, $results);
        $this->assertSame($grant, $results->{$this->accessDatabase});
        $this->assertSame($grant, $accessLevel);

        $this->tearDownAccessTest();
    }

    public function testClearDatabaseAccessLevel()
    {
        $this->setUpAccessTest();
        $grant = 'rw';

        $this->schemaManager->setDatabaseAccessLevel($this->userName, $this->accessDatabase, $grant);
        $accessLevel = $this->schemaManager->getDatabaseAccessLevel($this->userName, $this->accessDatabase);
        $this->assertSame($grant, $accessLevel);

        $result = $this->schemaManager->clearDatabaseAccessLevel($this->userName, $this->accessDatabase);
        $accessLevel = $this->schemaManager->getDatabaseAccessLevel($this->userName, $this->accessDatabase);

        $this->assertTrue($result);
        $this->assertSame('none', $accessLevel);

        $this->tearDownAccessTest();
    }

    protected function setUpAccessTest()
    {
        if (! $this->schemaManager->hasDatabase($this->accessDatabase)) {
            $this->schemaManager->createDatabase($this->accessDatabase);
        }
    }

    protected function tearDownAccessTest()
    {
        $this->schemaManager->deleteDatabase($this->accessDatabase);
    }
}
