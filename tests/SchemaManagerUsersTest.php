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

    expect($user->user)->toBe($name);
});

test('get users', function () {
    $users = $this->schemaManager->getUsers();
    expect($users)->toBeArray();
    $this->assertObjectHasProperty('user', $users[0]);
});

test('has user', function () {
    $result = $this->schemaManager->hasUser('root');
    expect($result)->toBeTrue();

    $result = $this->schemaManager->hasUser('nonExistingUser');
    expect($result)->toBeFalse();
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
    expect($created->user)->toBe($user['user']);

    $this->schemaManager->deleteUser($user['user']);
    $checkDeleted = $this->schemaManager->hasUser($user['user']);
    expect($checkDeleted)->toBeFalse();
});

test('update user', function () {
    $newUserData = [
        'user' => $this->userName,
        'active' => false,
    ];
    $updated = $this->schemaManager->updateUser($this->userName, $newUserData);

    expect($updated->user)->toBe($newUserData['user']);
});

test('replace user', function () {
    $newUserData = [
        'user' => 'newUserName',
        'active' => false,
    ];
    $replaced = $this->schemaManager->replaceUser($this->userName, $newUserData);

    expect($replaced->user)->toBe($this->userName);
});

test('get database access level', function () {
    $accessLevel = $this->schemaManager->getDatabaseAccessLevel('root', '_system');

    expect($accessLevel)->toBe('rw');
});

test('set database access level', function () {
    setUpAccessTest();
    $grant = 'rw';

    $results = $this->schemaManager->setDatabaseAccessLevel($this->userName, $this->accessDatabase, $grant);
    $accessLevel = $this->schemaManager->getDatabaseAccessLevel($this->userName, $this->accessDatabase);

    $this->assertObjectHasProperty($this->accessDatabase, $results);
    expect($results->{$this->accessDatabase})->toBe($grant);
    expect($accessLevel)->toBe($grant);

    tearDownAccessTest();
});

test('clear database access level', function () {
    setUpAccessTest();
    $grant = 'rw';

    $this->schemaManager->setDatabaseAccessLevel($this->userName, $this->accessDatabase, $grant);
    $accessLevel = $this->schemaManager->getDatabaseAccessLevel($this->userName, $this->accessDatabase);
    expect($accessLevel)->toBe($grant);

    $result = $this->schemaManager->clearDatabaseAccessLevel($this->userName, $this->accessDatabase);
    $accessLevel = $this->schemaManager->getDatabaseAccessLevel($this->userName, $this->accessDatabase);

    expect($result)->toBeTrue();
    expect($accessLevel)->toBe('none');

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
