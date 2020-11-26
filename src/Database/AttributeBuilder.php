<?php
namespace CoasterCommerce\Core\Database;

use CoasterCommerce\Core\Contracts\Cart;
use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Product\AttributeCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Exception;

class AttributeBuilder extends Builder
{

    protected $_eavDatatypeAttributeIds;

    /**
     * @param array $values
     * @return bool
     */
    public function insert(array $values)
    {
        return (bool) $this->insertGetId($values);
    }

    /**
     * Handle saving additional eav attributes
     * @param  array  $values
     * @param  string|null  $sequence
     * @return int
     */
    public function insertGetId(array $values, $sequence = null)
    {
        $eavInserts = $this->_filterEavAttributes($values);
        $this->_removeVirtualAttributes($values);
        $id = parent::insertGetId($values);
        if ($eavInserts) {
            foreach ($eavInserts as $typeClass => $eavTypeInserts) {
                $eavRowData = [];
                foreach ($eavTypeInserts as $attributeId => $value) {
                    if (!is_null($value)) {
                        $eavRowData[] = [
                            'product_id' => $id,
                            'attribute_id' => $attributeId,
                            'value' => $value
                        ];
                    }
                }
                /** @var Model $eavModel */
                $eavModel = new $typeClass;
                $eavModel->insert($eavRowData);
            }
        }
        return $id;
    }

    /**
     * Handle saving additional eav attributes
     * @param array $values
     * @return int
     * @throws Exception
     */
    public function update(array $values)
    {
        if ($eavUpdates = $this->_filterEavAttributes($values)) {
            $productCollection = $this->get();
            if ($productIds = $productCollection->modelKeys()) {
                foreach ($eavUpdates as $typeClass => $eavTypeUpdates) {
                    foreach ($eavTypeUpdates as $attributeId => $value) {
                        /** @var Model $eavModel */
                        $eavModel = new $typeClass;
                        if (is_null($value)) {
                            $eavModel->whereIn('product_id', $productIds)->where('attribute_id', $attributeId)->delete();
                        } else {
                            $updateIds = $eavModel->whereIn('product_id', $productIds)->where('attribute_id', $attributeId)
                                ->select('product_id')->pluck('product_id')->toArray();
                            $insertIds = array_diff($productIds, $updateIds);
                            if ($updateIds) {
                                $eavModel->whereIn('product_id', $productIds)->where('attribute_id', $attributeId)->update(['value' => $value]);
                            }
                            if ($insertIds) {
                                $eavModel->insert(array_map(function ($insertId) use ($attributeId, $value) {
                                    return ['product_id' => $insertId, 'attribute_id' => $attributeId, 'value' => $value];
                                }, $insertIds));
                            }
                        }
                    }
                }
            }
        }
        // run normal update on main table and return number of affected rows
        $this->_removeVirtualAttributes($values);
        $updated = parent::update($values);
        return !empty($productIds) ? count($productIds) : $updated;
    }

    /**
     * @param array $columns
     * @return Builder[]|Collection
     * @throws Exception
     */
    public function get($columns = ['*'])
    {
        if ($columns != ['*']) {
            $columns = array_unique(array_merge(AttributeCache::getRequiredAttributes($columns), ['id'])); // always need id
        }
        $this->_generateRequiredEavAttributes($columns);
        $defaultColumns = $columns == ['*'] ? $columns : array_intersect(AttributeCache::getDefaultAttributeCodes(), $columns);
        return parent::get($defaultColumns);
    }

    /**
     * @param array $columns
     */
    protected function _generateRequiredEavAttributes($columns)
    {
        if (is_null($this->_eavDatatypeAttributeIds)) {
            if ($this->getQuery()->columns) {
                $columns = [];
                foreach ($this->getQuery()->columns as $column) {
                    if (strpos($column, 'cc_products.') === 0) {
                        $columns[] = str_replace('cc_products.', '', $column);
                    }
                }
            }
            $eavAttributes = $columns == ['*'] ? AttributeCache::getEavAttributes() : AttributeCache::getEavAttributes()->whereIn('code', $columns);
            $this->_eavDatatypeAttributeIds = [];
            foreach ($eavAttributes as $eavAttribute) {
                $this->_eavDatatypeAttributeIds[$eavAttribute->eav->datatype][] = $eavAttribute->id;
            }
        }
    }

    /**
     * @param string $column
     * @param string $direction
     * @return Builder
     */
    public function orderBy($column, $direction = 'asc')
    {
        if (strpos($column, '.') === false) {
            $eavAttributes = AttributeCache::getEavAttributes();
            if ($column == 'price') {
                $priceIndexTable = (new Product\SearchIndex\Price())->getTable();
                $productTable = $this->model->getTable();
                $this->leftJoin($priceIndexTable . ' as p_i', function ($join) use($productTable) {
                    $join->on('p_i.product_id', '=', $productTable . '.id');
                })->whereNull('p_i.customer_id')->whereNull('p_i.group_id');
                if ($customer = app(Cart::class)->getCustomer()) {
                    $this->leftJoin($priceIndexTable . ' as p_ig', function ($join) use($productTable, $customer) {
                        $join->on('p_ig.product_id', '=', $productTable . '.id')
                            ->on('p_ig.group_id', '=', $this->query->raw($customer->group_id));
                    })->whereNull('p_ig.customer_id')->leftJoin($priceIndexTable . ' as p_ic', function ($join) use($productTable, $customer) {
                        $join->on('p_ic.product_id', '=', $productTable . '.id')
                            ->on('p_ic.customer_id', '=', $this->query->raw($customer->id));
                    })->orderBy($this->query->raw("IFNULL(p_ic.min_price, IFNULL(p_ig.min_price, p_i.min_price))"), $direction);
                } else {
                    $this->orderBy('p_i.min_price', $direction);
                }
                if (!$this->query->columns) {
                    $this->select([$productTable . '.*']);
                }
                return $this;
            } elseif ($eavAttributes->offsetExists($column)) {
                $attribute = $eavAttributes->offsetGet($column);
                $typeModel = AttributeCache::$eavTypes->typeModel($attribute->eav->datatype);
                $eavTable = (new $typeModel)->getTable();
                $productTable = $this->model->getTable();
                $this->leftJoin($eavTable . ' as ' . $column, function ($join) use($column, $productTable, $attribute) {
                    $join->on($column . '.product_id', '=', $productTable . '.id');
                    $join->on($column . '.attribute_id', '=', $this->query->raw("'". $attribute->id ."'"));
                })->orderBy($column . '.value', $direction);
                if (!$this->query->columns) {
                    $this->select([$productTable . '.*']);
                }
                return $this;
            }
        }
        return parent::orderBy($column, $direction);
    }

    /**
     * @param array $columns
     * @return Builder
     */
    public function select($columns = ['*'])
    {
        parent::select($columns);
        $this->query->columns = $this->_convertToFullTableName($this->query->columns);
        return $this;
    }

    /**
     * Needed because of the possible join in the orderBy function
     * @param string|array $columns
     * @return string
     */
    protected function _convertToFullTableName($columns)
    {
        if (!is_object($columns)) {
            $isArray = is_array($columns);
            $columns = $isArray ? $columns : [$columns];
            foreach ($columns as $k => $column) {
                if (is_string($column) &&  strpos($column, '.') === false) {
                    $columns[$k] = $this->model->getTable() . '.' . $column;
                }
            }
            return $isArray ? $columns : $columns[0];
        }
        return $columns;
    }

    /**
     * Load eav and virtual product attributes when loading the product model
     * @param  array  $models
     * @return array
     * @throws Exception
     */
    public function eagerLoadRelations(array $models)
    {
        if ($models) {
            // figure out required attributes (if not set already through get(), can come from earlier select() or relation query)
            $this->_generateRequiredEavAttributes([]);
            // add eav relations
            foreach ($this->_eavDatatypeAttributeIds as $dataType => $eavAttributeId) {
                $eavRelation = AttributeCache::$eavTypes->typeRelation($dataType);
                if (!array_key_exists($eavRelation, $this->eagerLoad)) {
                    $this->with($eavRelation);
                }
            }
        }
        $models = parent::eagerLoadRelations($models);
        if ($this->eagerLoad) { // on product->save() queries are loaded without relations (don't load attributes in these cases)
            foreach ($models as $model) {
                /** @var Product $model */
                $model->loadProductAttributes();
            }
        }
        return $models;
    }

    /**
     * Loads eav relations for attributes as these don't exist on model
     * @param  string  $name
     * @return Relation
     */
    public function getRelation($name)
    {
        $relation = Relation::noConstraints(function () use ($name) {
            try {
                $eavRelationClasses = AttributeCache::$eavTypes->relationClasses();
                if (array_key_exists($name, $eavRelationClasses)) {
                    $eavDatatype = AttributeCache::$eavTypes->relationType($name);
                    return $this->getModel()->newInstance()
                        ->hasMany($eavRelationClasses[$name])
                        ->whereIn('attribute_id', $this->_eavDatatypeAttributeIds[$eavDatatype]);
                }
                return $this->getModel()->newInstance()->$name();
            } catch (\BadMethodCallException $e) {
                throw RelationNotFoundException::make($this->getModel(), $name);
            }
        });

        $nested = $this->relationsNestedUnder($name);
        if (count($nested) > 0) {
            $relation->getQuery()->with($nested);
        }
        return $relation;
    }

    /**
     * Changes scope of "where" to query eav tables as well
     * @param string $method
     * @param mixed ...$parameters
     * @return false|Builder
     */
    protected function _addWhereToScope($method, ...$parameters)
    {
        if (is_string($parameters[0]) && $attribute = AttributeCache::getIfEavAttribute($parameters[0])) {
            $isOr = stripos($method, 'or') !== false;
            $isNull = stripos($method, 'wherenull') !== false;
            $parameters = array_slice($parameters, 1);
            if ($isNull) {
                // new relation query for all nulls
                $whereHas = $isOr ? 'orWhereDoesntHave' : 'whereDoesntHave';
            } else {
                // if relation query for attribute exists append additional wheres and return
                foreach ($this->getQuery()->wheres as $where) {
                    if (array_key_exists('query', $where) &&
                        ($where['boolean'] == 'and' || $isOr) &&
                        ((!$isNull && $where['type'] == 'Exists') || (($isNull && $where['type'] == 'NotExists')))
                    ) {
                        /** @var QueryBuilder $subQuery */
                        $subQuery = $where['query'];
                        $typeModel = AttributeCache::$eavTypes->typeModel($attribute->eav->datatype);
                        /** @var Model $eavModel */
                        $eavModel = (new $typeModel);
                        if ($subQuery->from == $eavModel->getTable() && $attribute->id == $subQuery->wheres[1]['value']) {
                            $subQuery->{$method}('value', ...$parameters);
                            $this->getQuery()->setBindings($this->_redoBindings($this->getQuery()));
                            return $this;
                        }
                    }
                }
                // otherwise new relation query
                $whereHas = $isOr ? 'orWhereHas' : 'whereHas';
            }
            return $this->{$whereHas}(AttributeCache::$eavTypes->typeRelation($attribute->eav->datatype), function ($q) use ($method, $attribute, $parameters, $isNull) {
                /** @var QueryBuilder $q */
                $q->where('attribute_id', $attribute->id);
                if (!$isNull) {
                    // or is on whereHas, so should be remove here
                    $method = stripos($method, 'or') === 0 ? substr(lcfirst($method), 2) : $method;
                    $q->{$method}('value', ...$parameters);
                }
            });
        }
        return false;
    }

    /**
     * @param QueryBuilder $query
     * @return array
     */
    protected function _redoBindings($query)
    {
        $bindings = [];
        foreach ($query->wheres as $where) {
            if (array_key_exists('query', $where)) {
                $bindings = array_merge($bindings, $this->_redoBindings($where['query']));
            } elseif (array_key_exists('value', $where)) {
                $bindings[] = $where['value'];
            }
        }
        return $bindings;
    }

    /**
     * Used in product attributes changes, have a couple of uses:
     * Strips out eav attributes from main table columns
     * Also converts collection values to database values (for custom attribute models)
     * @param array $values
     * @return array
     */
    protected function _filterEavAttributes(&$values)
    {
        $eavUpdates = [];
        foreach ($values as $column => &$value) {
            $attribute = AttributeCache::getProductAttributes()->offsetGet($column);
            $value = AttributeCache::$modelTypes->collectionToDatabase($attribute->model, $value);
            if ($attribute->eav) {
                // convert eav values to
                $eavUpdates[AttributeCache::$eavTypes->typeModel($attribute->eav->datatype)][$attribute->id] = $value;
                unset($values[$column]);
            }
        }
        return $eavUpdates;
    }

    /**
     * @param $values
     */
    protected function _removeVirtualAttributes(&$values)
    {
        foreach ($values as $column => $value) {
            if ($attribute = AttributeCache::getIfVirtualAttribute($column)) {
                unset($values[$column]);
            }
        }
    }

    /*
     * Overridden eloquent where functions to check for eav attributes
     */

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($result = $this->_addWhereToScope('where', ...func_get_args())) {
            return $result;
        }
        return parent::where($this->_convertToFullTableName($column), $operator, $value, $boolean);
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        if ($result = $this->_addWhereToScope('orWhere', ...func_get_args())) {
            return $result;
        }
        return parent::orWhere($this->_convertToFullTableName($column), $operator, $value);
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        if ($result = $this->_addWhereToScope('whereIn', ...func_get_args())) {
            return $result;
        }
        return parent::whereIn($this->_convertToFullTableName($column), $values, $boolean, $not);
    }

    public function orWhereIn($column, $values)
    {
        if ($result = $this->_addWhereToScope('orWhereIn', ...func_get_args())) {
            return $result;
        }
        return parent::orWhereIn($this->_convertToFullTableName($column), $values);
    }

    public function whereNull($columns, $boolean = 'and', $not = false)
    {
        if ($result = $this->_addWhereToScope('whereNull', ...func_get_args())) {
            return $result;
        }
        return parent::whereNull($this->_convertToFullTableName($columns), $boolean, $not);
    }

    public function orWhereNull($column)
    {
        if ($result = $this->_addWhereToScope('orWhereNull', ...func_get_args())) {
            return $result;
        }
        return parent::orWhereNull($this->_convertToFullTableName($column));
    }

    public function whereNotNull($column, $boolean = 'and')
    {
        if ($result = $this->_addWhereToScope('whereNotNull', ...func_get_args())) {
            return $result;
        }
        return parent::whereNotNull($this->_convertToFullTableName($column), $boolean);
    }

    public function orWhereNotNull($column)
    {
        if ($result = $this->_addWhereToScope('orWhereNotNull', ...func_get_args())) {
            return $result;
        }
        return parent::orWhereNotNull($this->_convertToFullTableName($column));
    }

    public function whereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        if ($result = $this->_addWhereToScope('whereBetween', ...func_get_args())) {
            return $result;
        }
        return parent::whereBetween($this->_convertToFullTableName($column), $values, $boolean, $not);
    }

    public function orWhereBetween($column, array $values)
    {
        if ($result = $this->_addWhereToScope('orWhereBetween', ...func_get_args())) {
            return $result;
        }
        return parent::orWhereBetween($this->_convertToFullTableName($column), $values);
    }

}