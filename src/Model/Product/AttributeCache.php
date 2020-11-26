<?php

namespace CoasterCommerce\Core\Model\Product;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Exception;

class AttributeCache
{

    /**
     * @var Collection|EloquentCollection
     */
    public static $productAttributes;

    /**
     * @var array
     */
    public static $productAttributesArray;

    /**
     * @var Collection|EloquentCollection
     */
    public static $eavAttributes;

    /**
     * @var Collection|EloquentCollection
     */
    public static $virtualAttributes;

    /**
     * @var array
     */
    public static $defaultAttributeCodes;

    /**
     * @var Attribute\EavTypes
     */
    public static $eavTypes;

    /**
     * @var Attribute\ModelTypes
     */
    public static $modelTypes;

    /**
     * @var Attribute\FrontendTypes
     */
    public static $frontendTypes;

    /**
     * @return Collection|EloquentCollection
     */
    public static function getProductAttributes()
    {
        if (is_null(static::$productAttributes)) {
            static::$productAttributes = (new Attribute)->with(['eav', 'meta'])->get()->keyBy('code');
        }
        return static::$productAttributes;
    }

    /**
     * @return array
     */
    public static function getProductAttributesArray()
    {
        if (is_null(static::$productAttributesArray)) {
            static::$productAttributesArray = static::getProductAttributes()->toArray();
        }
        return static::$productAttributesArray;
    }

    /**
     * @return array
     */
    public static function getProductAttributeNullArray()
    {
        $productAttributes = [];
        foreach (static::getProductAttributes() as $code => $productAttribute) {
            $productAttributes[$code] = null;
        }
        return $productAttributes;
    }

    /**
     * @return Collection|EloquentCollection
     */
    public static function getEavAttributes()
    {
        if (is_null(static::$eavAttributes)) {
            static::$eavAttributes = static::getProductAttributes()->where('type', 'eav');
        }
        return static::$eavAttributes;
    }

    /**
     * @return Collection|EloquentCollection
     */
    public static function getVirtualAttributes()
    {
        if (is_null(static::$virtualAttributes)) {
            static::$virtualAttributes = static::getProductAttributes()->where('type', 'virtual');
        }
        return static::$virtualAttributes;
    }

    /**
     * @return array
     */
    public static function getDefaultAttributeCodes()
    {
        if (is_null(static::$defaultAttributeCodes)) {
            static::$defaultAttributeCodes = static::getProductAttributes()->where('type', 'default')->pluck('code')->toArray();
        }
        return static::$defaultAttributeCodes;
    }

    /**
     * @param string $code
     * @return Model
     */
    public static function getIfEavAttribute($code)
    {
        return static::getEavAttributes()->offsetExists($code) ? static::getEavAttributes()->offsetGet($code) : null;
    }

    /**
     * @param string $code
     * @return Model
     */
    public static function getIfVirtualAttribute($code)
    {
        return static::getVirtualAttributes()->offsetExists($code) ? static::getVirtualAttributes()->offsetGet($code) : null;
    }

    /**
     * @param array $attributes
     * @return array
     */
    public static function getRequiredAttributes($attributes)
    {
        $requiredAttributes = $attributes;
        foreach ($attributes as $attribute) {
            if ($virtualAttribute = static::getIfVirtualAttribute($attribute)) {
                $requiredForVirtual = AttributeCache::$modelTypes->columnsForVirtual($virtualAttribute->model);
                $requiredAttributes = array_merge(
                    static::getRequiredAttributes(array_diff($requiredForVirtual, $attributes)),
                    $requiredAttributes
                );
            }
        }
        return $requiredAttributes;
    }

    /**
     * @param string $code
     * @param string $key
     * @return EloquentCollection|string
     */
    public static function getMeta($code, $key = null)
    {
        /** @var EloquentCollection $meta */
        if (static::getProductAttributes()->offsetExists($code)) {
            $meta = static::getProductAttributes()->offsetGet($code)->meta->keyBy('key');
            if ($key) {
                return $meta->offsetExists($key) ? $meta->offsetGet($key)->value : collect([]);
            } else {
                return $meta;
            }
        }
        return is_null($key) ? collect([]) : null;
    }

}
