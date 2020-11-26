<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Model;

class JsonModel extends AbstractModel
{

    /**
     * @param mixed $value
     * @return array
     */
    public function databaseToCollection($value)
    {
        return json_decode($value, true) ?: [];
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function collectionToDatabase($value)
    {
        return json_encode($value);
    }

}
