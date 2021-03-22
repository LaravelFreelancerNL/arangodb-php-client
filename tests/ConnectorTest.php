<?php

declare(strict_types=1);

namespace Tests;

class ConnectorTest extends TestCase
{
    public function testGetConfig()
    {
        $defaultConfig = [
            'host' => 'http://localhost',
            'port' => '8529',
            'AuthUser' => 'root',
            'AuthPassword' => null,
            'AuthType' => 'basic',
            'base_uri' => 'http://localhost:8529'
        ];

        $config = $this->connector->getConfig();
        $this->assertSame($defaultConfig, $config);
    }

    public function testRequest()
    {
        $result = $this->connector->request('get', '/_api/version',[]);

        $this->assertSame('arango', $result['server']);
        $this->assertSame('community', $result['license']);
        $this->assertIsString($result['version']);
    }

    public function testGetUser()
    {
        $user = $this->connector->getUser();
        $this->assertSame('root', $user);
    }

}