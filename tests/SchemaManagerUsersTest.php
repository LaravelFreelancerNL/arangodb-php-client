<?php

uses(Tests\TestCase::class);

declare(strict_types=1);
beforeEach(function () {
    $user = [
        'user' => $this->userName,
        'password' => 'yee random pw',
    ];

    if (!$this->schemaManager->hasUser($this->userName)) {
        $this->schemaManager->createUser($user);
    }
});

afterEach(function () {
    if ($this->schemaManager->hasUser($this->userName)) {
        $this->schemaManager->deleteUser($this->userName);
    }
});


test('get user', function () {
    $name = 'root';
    $user = $this->schemaManager->getUser($name);

    $this->assertSame($name, $user->user);
});

test('get users', function () {
    $users = $this->schemaManager->getUsers();
    $this->assertIsArray($users);
    $this->assertObjectHasProperty('user', $users[0]);
});

test('has user', function () {
    $result = $this->schemaManager->hasUser('root');
    $this->assertTrue($result);

    $result = $this->schemaManager->hasUser('nonExistingUser');
    $this->assertFalse($result);
});

test('create and delete user', function () {
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
});

test('update user', function () {
    $newUserData = [
        'user' => $this->userName,
        'active' => false,
    ];
    $updated = $this->schemaManager->updateUser($this->userName, $newUserData);

    $this->assertSame($newUserData['user'], $updated->user);
});

test('replace user', function () {
    $newUserData = [
        'user' => 'newUserName',
        'active' => false,
    ];
    $replaced = $this->schemaManager->replaceUser($this->userName, $newUserData);

    $this->assertSame($this->userName, $replaced->user);
});

test('get database access level', function () {
    $accessLevel = $this->schemaManager->getDatabaseAccessLevel('root', '_system');

    $this->assertSame('rw', $accessLevel);
});

test('set database access level', function () {
    setUpAccessTest();
    $grant = 'rw';

    $results = $this->schemaManager->setDatabaseAccessLevel($this->userName, $this->accessDatabase, $grant);
    $accessLevel = $this->schemaManager->getDatabaseAccessLevel($this->userName, $this->accessDatabase);

    $this->assertObjectHasProperty($this->accessDatabase, $results);
    $this->assertSame($grant, $results->{$this->accessDatabase});
    $this->assertSame($grant, $accessLevel);

    tearDownAccessTest();
});

test('clear database access level', function () {
    setUpAccessTest();
    $grant = 'rw';

    $this->schemaManager->setDatabaseAccessLevel($this->userName, $this->accessDatabase, $grant);
    $accessLevel = $this->schemaManager->getDatabaseAccessLevel($this->userName, $this->accessDatabase);
    $this->assertSame($grant, $accessLevel);

    $result = $this->schemaManager->clearDatabaseAccessLevel($this->userName, $this->accessDatabase);
    $accessLevel = $this->schemaManager->getDatabaseAccessLevel($this->userName, $this->accessDatabase);

    $this->assertTrue($result);
    $this->assertSame('none', $accessLevel);

    tearDownAccessTest();
});

// Helpers
function setUpAccessTest()
{
    if (!test()->schemaManager->hasDatabase(test()->accessDatabase)) {
        test()->schemaManager->createDatabase(test()->accessDatabase);
    }
}

function tearDownAccessTest()
{
    test()->schemaManager->deleteDatabase(test()->accessDatabase);
}
