<?php

declare(strict_types=1);

namespace Tests;

use ArangoClient\Exceptions\ArangoException;
use GuzzleHttp\Exception\GuzzleException;


class SchemaClientViewsTest extends TestCase
{
    protected array $view = [
        'name' => 'testViewBasics',
        'type' => 'arangosearch'
    ];

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->schemaClient->hasView($this->view['name'])) {
            $this->schemaClient->createView($this->view);
        }
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->schemaClient->hasView($this->view['name'])) {
            $this->schemaClient->deleteView($this->view['name']);
        }
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetViews()
    {
        $views = $this->schemaClient->getViews();

        $this->assertSame($this->view['name'], $views[0]['name']);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetView()
    {
        $view = $this->schemaClient->getView($this->view['name']);

        $this->assertSame($this->view['name'], $view['name']);
        $this->assertArrayHasKey('type', $view);
        $this->assertArrayHasKey('links', $view);
    }


    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testGetViewProperties()
    {
        $view = $this->schemaClient->getViewProperties($this->view['name']);

        $this->assertSame($this->view['name'], $view['name']);
        $this->assertArrayHasKey('type', $view);
        $this->assertArrayHasKey('links', $view);
    }


    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testHasView()
    {
        $result = $this->schemaClient->hasView($this->view['name']);
        $this->assertTrue($result);

        $result = $this->schemaClient->hasView('someNoneExistingView');
        $this->assertFalse($result);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testRenameView()
    {
        $newName = 'newName';
        $result = $this->schemaClient->renameView($this->view['name'], $newName);
        $this->assertSame($newName, $result['name']);

        $this->schemaClient->deleteView($newName);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testUpdateView()
    {
        $newViewProps = [
            'cleanupIntervalStep' => 3,
            'primarySort' => 'email'
        ];
        $result = $this->schemaClient->updateView($this->view['name'], $newViewProps);

        $this->assertSame(3, $result['cleanupIntervalStep']);
    }

    public function testReplaceView()
    {
        $newViewProps = [
            'primarySort' => [[
                'field' => 'email',
                'direction' => 'desc'
            ]]
        ];
        $newView = $this->schemaClient->replaceView($this->view['name'], $newViewProps);

        $this->assertSame($newViewProps['primarySort'][0]['field'], $newView['primarySort'][0]['field']);
        $this->assertFalse($newView['primarySort'][0]['asc']);
    }

    /**
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function testCreateAndDeleteView()
    {
        $view = [
            'name' => 'coolnewview'
        ];
        $created = $this->schemaClient->createView($view);
        $this->assertArrayHasKey('name', $created);
        $this->assertSame($view['name'], $created['name']);

        $deleted = $this->schemaClient->deleteView($view['name']);
        $this->assertTrue($deleted);
    }
}