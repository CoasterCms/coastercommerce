<?php
namespace CoasterCommerce\Core\Database;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\Migration as BaseMigration;
use Illuminate\Database\Schema\Builder;

class Migration extends BaseMigration
{

    /**
     * @var DatabaseManager
     */
    protected $_db;

    /**
     * @var Builder
     */
    protected $_schema;

    /**
     * Migration constructor.
     */
    public function __construct()
    {
        /** @var DatabaseManager $db */
        $db = app('db');

        $this->_db = $db;
        $this->_schema = $this->_db->connection()->getSchemaBuilder();

        $this->_schema->blueprintResolver(function ($table, $callback) {
            $blueprint = new Blueprint($table, $callback);
            $blueprint->setDatabaseManager($this->_db);
            return $blueprint;
        });
    }

}
