<?php

declare(strict_types=1);

namespace ArangoClient\Schema;

use ArangoClient\ArangoClient;
use ArangoClient\Manager;

class SchemaManager extends Manager
{
    use ManagesCollections;
    use ManagesDatabases;
    use ManagesGraphs;
    use ManagesIndexes;
    use ManagesUsers;
    use ManagesViews;

    public function __construct(protected ArangoClient $arangoClient)
    {
    }
}
