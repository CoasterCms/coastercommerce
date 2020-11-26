<?php

namespace CoasterCommerce\Core\Model\Product\Attribute;

class EavTypes
{

    /**
     * @var string
     */
    protected $_relationPrefix;

    /**
     * @var array
     */
    protected $_relationClasses;

    /**
     * @var array
     */
    protected $_relationTypes;

    /**
     * @var array
     */
    protected $_typeRelations;

    /**
     * @var array
     */
    protected $_types;

    /**
     * EavTypes constructor.
     * @param array $types
     * @param string $relationPrefix
     */
    public function __construct($types, $relationPrefix = 'attribute_')
    {
        $this->_relationPrefix = $relationPrefix;
        $this->_types = $types;
    }

    /**
     * @param string $name
     * @param string $eavModel
     */
    public function setType($name, $eavModel)
    {
        $this->_types[$name] = $eavModel;
    }

    /**
     *
     */
    public function refreshRelations()
    {
        $this->_relationClasses = null;
        $this->_relationTypes = null;
        $this->_typeRelations = null;
    }

    /**
     * @return array
     */
    public function types()
    {
        return $this->_types;
    }

    /**
     * @param string $type
     * @return string
     */
    public function typeModel($type)
    {
        return $this->_types[$type];
    }

    /**
     * @return array
     */
    public function relationClasses()
    {
        if (is_null($this->_relationClasses)) {
            foreach ($this->_types as $type => $eavModel) {
                $this->_relationClasses[$this->_relationPrefix . $type] = $eavModel;
            }
        }
        return $this->_relationClasses;
    }

    /**
     * @param string $datatype
     * @return string
     */
    public function typeRelation($datatype)
    {
        if (is_null($this->_typeRelations)) {
            foreach ($this->_types as $type => $eavModel) {
                $this->_typeRelations[$type] = $this->_relationPrefix . $type;
            }
        }
        return $this->_typeRelations[$datatype];
    }

    /**
     * @param string $relation
     * @return string
     */
    public function relationType($relation)
    {
        if (is_null($this->_relationTypes)) {
            foreach ($this->_types as $type => $eavModel) {
                $this->_relationTypes[$this->_relationPrefix . $type] = $type;
            }
        }
        return $this->_relationTypes[$relation];
    }

    /**
     * @return array
     */
    public function selectOptions()
    {
        $types = array_keys($this->_types);
        return array_combine($types, $types);
    }

}
