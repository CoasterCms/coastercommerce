<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Model;

use CoasterCommerce\Core\Model\Product\Attribute\Model\FileModel\FileValue;

class FileModel extends AbstractModel
{

    /**
     * @param string $value
     * @return FileValue
     */
    public function databaseToCollection($value)
    {
        return new FileValue($value);
    }

    /**
     * @param FileValue $value
     * @return string
     */
    public function collectionToDatabase($value)
    {
        return $value ? $value->toJson() : null;
    }

}
