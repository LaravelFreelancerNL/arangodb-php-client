<?php

declare(strict_types=1);

namespace ArangoClient\Schema;

use ArangoClient\Connector;

class SchemaClient
{
    use ManagesDatabases;
    use ManagesCollections;
    use ManagesIndexes;
    use ManagesViews;
    use ManagesUsers;

    /**
     * @var Connector
     */
    protected Connector $connector;

    /**
     * Documents constructor.
     * @param  Connector  $connector
     */
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }
}
