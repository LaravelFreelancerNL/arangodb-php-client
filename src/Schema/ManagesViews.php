<?php

namespace ArangoClient\Schema;

use ArangoClient\Connector;
use ArangoClient\Exceptions\ArangoException;
use GuzzleHttp\Exception\GuzzleException;

/*
 * @see https://www.arangodb.com/docs/stable/http/views.html
 */
trait ManagesViews
{
    protected Connector $connector;

    /**
     * @see https://www.arangodb.com/docs/stable/http/views-arangosearch.html#create-an-arangosearch-view
     *
     * @param  array<mixed>  $view
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function createView(array $view): array
    {
        $view['type'] = isset($view['type']) ? (string) $view['type'] : 'arangosearch';

        $uri = '/_api/view#' . $view['type'];
        $body = json_encode((object) $view);

        return (array) $this->connector->request('post', $uri, ['body' => $body]);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/views-arangosearch.html#drops-a-view
     *
     * @param  string  $name
     * @return bool
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function deleteView(string $name): bool
    {
        $uri = '/_api/view/' . $name;

        return (bool) $this->connector->request('delete', $uri);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/views-arangosearch.html#list-all-views
     *
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function getViews(): array
    {
        return (array) $this->connector->request('get', '/_api/view');
    }

    /**
     * Check for view existence
     *
     * @param  string  $name
     * @return bool
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function hasView(string $name): bool
    {
        $views = $this->getViews();
        return array_search($name, array_column($views, 'name'), true) !== false;
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/views-arangosearch.html#return-information-about-a-view
     *
     * @param  string  $name
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function getView(string $name): array
    {
        return $this->getViewProperties($name);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/views-arangosearch.html#read-properties-of-a-view
     *
     * @param  string  $name
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function getViewProperties(string $name): array
    {
        $uri = '/_api/view/' . $name . '/properties';
        return (array) $this->connector->request('get', $uri);
    }

    /**
     * @param  string  $old
     * @param  string  $new
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function renameView(string $old, string $new): array
    {
        $uri = '/_api/view/' . $old . '/rename';

        $body = json_encode((object) ['name' => $new]);
        $options = ['body' => $body];

        return (array) $this->connector->request('put', $uri, $options);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/views-arangosearch.html#partially-changes-properties-of-an-arangosearch-view
     *
     * @param  string  $name
     * @param  array<mixed>  $properties
     * @return array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function updateView(string $name, array $properties): array
    {
        // PrimarySort & primarySortCompression are immutable and will throw if we try to change it.
        // Use replaceView if you want to update these properties.
        if (isset($properties['primarySort'])) {
            unset($properties['primarySort']);
        }
        if (isset($properties['primarySortCompression'])) {
            unset($properties['primarySortCompression']);
        }

        $properties['type'] = isset($properties['type']) ? (string) $properties['type'] : 'arangosearch';

        $uri = '/_api/view/' . $name . '/properties#' . $properties['type'];
        $body = json_encode((object) $properties);
        $options = ['body' => $body];

        return (array) $this->connector->request('patch', $uri, $options);
    }

    /**
     * Replace an existing view. Use this to change immutable fields like primarySort. Note that
     * this is just a shorthand for delete(old)/create(new). ArangoDB will have to rebuild the view data.
     *
     * @see https://www.arangodb.com/docs/stable/http/views-arangosearch.html#create-an-arangosearch-view
     *
     * @param string $name
     * @param array<mixed> $newView
     * @return false|array<mixed>
     * @throws ArangoException
     * @throws GuzzleException
     */
    public function replaceView(string $name, array $newView)
    {
        if (! $this->hasView($name)) {
            return false;
        }
        $this->deleteView($name);

        // Enforce the view name
        $newView['name'] = $name;

        return $this->createView($newView);
    }
}
