<?php

namespace CoasterCommerce\Core\Model\Product\Attribute;

use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Product\Attribute;
use CoasterCommerce\Core\Model\Product\Attribute\Model\AbstractModel;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class ModelTypes
{

    /**
     * @var AbstractModel
     */
    protected $_models;

    /**
     * EavTypes constructor.
     * @param AbstractModel[] $types
     */
    public function __construct($types)
    {
        $this->_models = $types;
    }

    /**
     * @param string $name
     * @param AbstractModel $typeModel
     */
    public function setType($name, $typeModel)
    {
        $this->_models[$name] = $typeModel;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function databaseToCollection($name, $value)
    {
        return $name ? $this->_models[$name]->databaseToCollection($value) : $value;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function collectionToDatabase($name, $value)
    {
        return $name ? $this->_models[$name]->collectionToDatabase($value) : $value;
    }

    /**
     * @param string $name
     * @param array $productAttributes
     * @param Product $product
     * @return mixed
     * @throws Exception
     */
    public function processVirtual($name, $product, $productAttributes)
    {
        if (!$name) {
            throw new Exception('Virtual types must have a model set for value generation.');
        }
         return $this->_models[$name]->processVirtual($productAttributes, $product);
    }

    /**
     * @param string $name
     * @return array
     */
    public function columnsForVirtual($name)
    {
        return $this->_models[$name]->columnsForVirtual();
    }

    /**
     * @param Attribute $attribute
     * @param mixed $filterValue
     * @param Builder $query
     * @return Builder
     */
    public function filterQuery($attribute, $filterValue, $query)
    {
        return $this->_models[$attribute->model]->filterQuery($attribute, $filterValue, $query);
    }

}
