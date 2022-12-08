<?php

declare(strict_types=1);

namespace ArangoClient;

use ArangoClient\Admin\AdminManager;
use ArangoClient\Schema\SchemaManager;

trait HasManagers
{
    protected ?AdminManager $adminManager = null;

    protected ?SchemaManager $schemaManager = null;

    public function admin(): AdminManager
    {
        if (! (property_exists($this, 'adminManager') && $this->adminManager !== null)) {
            $this->adminManager = new AdminManager($this);
        }

        return $this->adminManager;
    }

    public function schema(): SchemaManager
    {
        if (! (property_exists($this, 'schemaManager') && $this->schemaManager !== null)) {
            $this->schemaManager = new SchemaManager($this);
        }

        return $this->schemaManager;
    }
}
