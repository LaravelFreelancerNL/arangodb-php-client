<?php

declare(strict_types=1);

namespace ArangoClient\Schema;

use ArangoClient\ArangoClient;
use ArangoClient\Manager;

class SchemaManager extends Manager
{
    use ManagesDatabases;
    use ManagesCollections;
    use ManagesIndexes;
    use ManagesViews;
    use ManagesUsers;
    use ManagesGraphs;

    protected ArangoClient $arangoClient;

    public function __construct(ArangoClient $arangoClient)
    {
        $this->arangoClient = $arangoClient;
    }
}
