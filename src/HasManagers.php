<?php

declare(strict_types=1);

namespace ArangoClient;

use ArangoClient\Admin\AdminManager;
use ArangoClient\Schema\SchemaManager;

trait HasManagers
{

    /**
     * @var AdminManager|null
     */
    protected ?AdminManager $adminManager = null;

    /**
     * @var SchemaManager|null
     */
    protected ?SchemaManager $schemaManager = null;

    /**
     * @return AdminManager
     */
    public function admin(): AdminManager
    {
        if (! isset($this->adminManager)) {
            $this->adminManager = new AdminManager($this);
        }
        return $this->adminManager;
    }

    public function schema(): SchemaManager
    {
        if (! isset($this->schemaManager)) {
            $this->schemaManager = new SchemaManager($this);
        }
        return $this->schemaManager;
    }
}
