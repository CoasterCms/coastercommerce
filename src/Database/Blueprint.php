<?php
namespace CoasterCommerce\Core\Database;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Blueprint as BaseBlueprint;

class Blueprint extends BaseBlueprint
{

    /**
     * @var DatabaseManager
     */
    protected $_db;

    /**
     * @param DatabaseManager $db
     */
    public function setDatabaseManager($db)
    {
        $this->_db = $db;
    }

    /**
     * Better timestamps for Mysql
     * @param int $precision
     */
    public function timestamps($precision = 0)
    {
        $this->timestamp('created_at')->useCurrent();
        $this->timestamp('updated_at')->default($this->_db->raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
    }

}
