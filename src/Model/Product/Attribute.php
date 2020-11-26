<?php

namespace CoasterCommerce\Core\Model\Product;

use CoasterCommerce\Core\Model\Product;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\View\View;

class Attribute extends Model
{

    public $table = 'cc_product_attributes';

    /**
     * @return HasOne
     */
    public function eav()
    {
        return $this->hasOne(Attribute\Eav::class);
    }

    /**
     * @return HasMany
     */
    public function meta()
    {
        return $this->hasMany(Attribute\Meta::class);
    }

    /**
     * @return bool
     */
    public function isSystem()
    {
        return $this->exists ? (bool) ($this->type != 'eav' ?: ($this->eav ? $this->eav->is_system : 0)) : false;
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        switch ($this->type) {
            case 'eav':
                return $this->eav->datatype;
            case 'default':
                /** @var Builder $schemaBuilder */
                $schemaBuilder = app('db')->getSchemaBuilder();
                return str_replace(['boolean'], ['integer'], $schemaBuilder->getColumnType('cc_products', $this->code));
            default:
                return $this->type;
        }
    }

    /**
     * @return Collection
     */
    public static function getAdminColumns()
    {
        return (new static)->where('admin_column', '>', 0)->orderBy('admin_column')->get();
    }

    /**
     * @return Collection
     */
    public static function getAdminFilters()
    {
        return (new static)->where('admin_filter', '>', 0)->orderBy('admin_filter')->get();
    }

    /**
     * @return bool
     */
    public static function hasMassUpdateAttributes()
    {
        return !!(new static)->where('admin_massupdate', '>', 0)->count();
    }

    /**
     * @return Collection
     */
    public static function getDataTableColumnsConf()
    {
        return static::getAdminColumns()->map(function ($attribute) {
            /** @var static $attribute */
            return $attribute->getDataTableColumnConf();
        })
            ->add(['data' => 'id', 'title' => 'Edit', 'orderable' => false, 'searchable' => false, 'render' => 'cc-edit'])
            ->add(['data' => 'search-data', 'visible' => false, 'searchable' => true]);
    }

    /**
     * @return array
     */
    public function getDataTableColumnConf()
    {
        return AttributeCache::$frontendTypes->dataTableColumnConf($this);
    }

    /**
     * @param Product $product
     * @return View
     */
    public function renderInput($product)
    {
        return AttributeCache::$frontendTypes->renderInput($this, $product);
    }

    /**
     * @return View
     */
    public function renderFilter()
    {
        return AttributeCache::$frontendTypes->renderFilter($this);
    }

    /**
     * id quick fix on new models
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (!$this->exists && $key == 'id') return null;
        return parent::getAttribute($key);
    }

    /**
     * @return string
     */
    public function id()
    {
        return 'attributes_';
    }

    /**
     * @return string
     */
    public function fieldName()
    {
        return 'attributes[' . $this->code . ']';
    }

    /**
     * @return string
     */
    public function fieldKey()
    {
        return 'attributes.' . $this->code;
    }

}
