<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Administration\AdministrationClient;
use ArangoClient\Exceptions\ArangoException;
use ArangoClient\Schema\SchemaClient;
use GuzzleHttp\Exception\GuzzleException;


class AdministrationClientTest extends TestCase
{
    protected AdministrationClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new AdministrationClient($this->connector);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testVersion()
    {
        $result = $this->client->version();

        $this->assertSame('arango', $result['server']);
        $this->assertSame('community', $result['license']);
        $this->assertIsString($result['version']);

    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testVersionWithDetails()
    {
        $result = $this->client->version(true);

        $this->assertSame('arango', $result['server']);
        $this->assertSame('community', $result['license']);
        $this->assertIsString($result['version']);
        $this->assertArrayHasKey('details', $result);
        $this->assertNotEmpty($result['details']);

    }

}