<?php

declare(strict_types=1);

namespace ArangoClient\Schema;

use ArangoClient\ArangoClient;
use ArangoClient\Exceptions\ArangoException;
use stdClass;

/*
 * @see https://www.arangodb.com/docs/stable/http/views.html
 */
trait ManagesViews
{
    protected ArangoClient $arangoClient;

    /**
     * @see https://www.arangodb.com/docs/stable/http/views-arangosearch.html#create-an-arangosearch-view
     *
     * @param  array<mixed>  $view
     * @return stdClass
     * @throws ArangoException
     */
    public function createView(array $view): stdClass
    {
        $view['type'] = isset($view['type']) ? (string) $view['type'] : 'arangosearch';

        $uri = '/_api/view#' . $view['type'];

        $options = [
            'body' => $view
        ];

        return  $this->arangoClient->request('post', $uri, $options);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/views-arangosearch.html#drops-a-view
     *
     * @param  string  $name
     * @return bool
     * @throws ArangoException
     */
    public function deleteView(string $name): bool
    {
        $uri = '/_api/view/' . $name;

        return (bool) $this->arangoClient->request('delete', $uri);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/views-arangosearch.html#list-all-views
     *
     * @return array<mixed>
     * @throws ArangoException
     */
    public function getViews(): array
    {
        $results = $this->arangoClient->request('get', '/_api/view');

        return (array) $results->result;
    }

    /**
     * Check for view existence
     *
     * @param  string  $name
     * @return bool
     * @throws ArangoException
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
     * @return stdClass
     * @throws ArangoException
     */
    public function getView(string $name): stdClass
    {
        return $this->getViewProperties($name);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/views-arangosearch.html#read-properties-of-a-view
     *
     * @param  string  $name
     * @return stdClass
     * @throws ArangoException
     */
    public function getViewProperties(string $name): stdClass
    {
        $uri = '/_api/view/' . $name . '/properties';

        return $this->arangoClient->request('get', $uri);
    }

    /**
     * @param  string  $old
     * @param  string  $new
     * @return stdClass
     * @throws ArangoException
     */
    public function renameView(string $old, string $new): stdClass
    {
        $uri = '/_api/view/' . $old . '/rename';

        $options = [
            'body' => [
                'name' => $new
            ]
        ];

        return $this->arangoClient->request('put', $uri, $options);
    }

    /**
     * @see https://www.arangodb.com/docs/stable/http/views-arangosearch.html#partially-changes-properties-of-an-arangosearch-view
     *
     * @param  string  $name
     * @param  array<mixed>  $properties
     * @return stdClass
     * @throws ArangoException
     */
    public function updateView(string $name, array $properties): stdClass
    {
        // PrimarySort & primarySortCompression are immutable and will throw if we try to change it.
        // Use replaceView if you want to update these properties.
        $removeKeys = ['primarySort', 'primarySortCompression'];
        $properties = array_diff_key($properties, array_flip($removeKeys));

        $properties['type'] = isset($properties['type']) ? (string) $properties['type'] : 'arangosearch';

        $uri = '/_api/view/' . $name . '/properties#' . $properties['type'];

        $options = [
            'body' => $properties
        ];

        return $this->arangoClient->request('patch', $uri, $options);
    }

    /**
     * Replace an existing view. Use this to change immutable fields like primarySort. Note that
     * this is just a shorthand for delete(old)/create(new). ArangoDB will have to rebuild the view data.
     *
     * @see https://www.arangodb.com/docs/stable/http/views-arangosearch.html#create-an-arangosearch-view
     *
     * @param string $name
     * @param array<mixed> $newView
     * @return stdClass|false
     * @throws ArangoException
     */
    public function replaceView(string $name, array $newView): stdClass|false
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
