<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\CatalogueUrls\CatalogueUrls;
use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Category;
use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Product\SearchIndex;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class ImportCatalogueController extends AbstractController
{

    public static $categoryNames;

    protected $_mediaImportLocation = 'import';

    /**
     *
     */
    protected function _init()
    {
        /** @var AdminMenu $adminMenu */
        $adminMenu = app('coaster-commerce.admin-menu');
        $adminMenu->getByName('Catalogue')->setActive();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request)
    {
        $path = $request->file('import-csv');
        $csvRecords = $path ? Reader::createFromPath($path)->setHeaderOffset(0) : [];

        $product_attributes = Product\Attribute::all();
        $product_codes = array_fill_keys($product_attributes->pluck('code')->toArray(), null); // valid product columns
        $variant_codes = array_fill_keys(['variation', 'enabled', 'sku', 'stock_qty', 'fixed_price', 'price', 'weight', 'image'], null); // valid variant columns
        $products = Product::with('variations')->get()->keyBy('sku');

        $media_attributes = array_fill_keys($product_attributes->where('model', 'file')->pluck('code')->toArray(), null); // for media uploads
        $select_attributes = $product_attributes->whereIn('frontend', ['select', 'select-multiple'])->pluck('code')->toArray(); // for saving select options
        $select_attribute_values = [];

        $products_added = 0;
        $products_updated = 0;
        $variants_added = 0;
        $variants_updated = 0;

        $productRecords = [];
        $variantRecords = [];
        $variantImages = [];
        $highestId = null;
        foreach ($csvRecords as $record) {
            if (array_key_exists('parent_sku', $record) && $record['parent_sku']) {
                $variantRecords[] = $record;
                if ($variantImage = array_key_exists('image', $record) ? $record['image'] : '') {
                    $variantImages[$record['parent_sku']][] = (!$variantImage && array_key_exists('images', $record)) ? $record['images'] : $variantImage;
                }
            } else {
                $productRecords[] = $record;
                if ($productId = (int) ($record['id'] ?? 0)) {
                    $highestId = max($highestId, $productId);
                }
            }
        }

        // set auto_inc id past highest product id to stop conflicts
        if ($highestId) {
            $dbName = \DB::connection()->getDatabaseName();
            $productsTable = (new Product())->getTable();
            $result = DB::select("SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $dbName . "' AND TABLE_NAME = '" . $productsTable .  "'");
            if ($result[0]->AUTO_INCREMENT <= $highestId) {
                DB::update("ALTER TABLE " . $productsTable .  " AUTO_INCREMENT = " . ($highestId + 1));
            }
        }

        foreach ($productRecords as $record) {

            $product = $products->offsetExists($record['sku']) ? $products->offsetGet($record['sku']) : new Product;

            try {
                // save attribute data for product codes that exist
                $record = array_filter($record, function ($value) {
                    return $value !== ''; // remove blank values
                });
                $attribute_record_data = array_intersect_key($record, $product_codes);
                $attribute_non_media_record_data = array_diff_key($attribute_record_data, $media_attributes);
                $product->forceFill($attribute_non_media_record_data)->save(['reindex' => false]);

                // upload & save media data (requires product id)
                $attribute_media_record_data = array_intersect_key($attribute_record_data, $media_attributes);
                foreach ($attribute_media_record_data as $attribute_code => $value) {
                    if ($attribute_code == 'images' && array_key_exists($record['sku'], $variantImages)) {
                        // add variant images to main product
                        $value = $value ? $value . ',' . implode(',', $variantImages[$record['sku']]) : '';
                    }
                    $attribute_media_record_data[$attribute_code] = $this->_importMedia($value, $attribute_code, $product);
                }
                $product->forceFill($attribute_media_record_data)->save(['reindex' => false]);

                // save product categories
                if (array_key_exists('categories', $record)) {
                    $this->_importCategories($product, $record['categories']);
                }

                // get additional select option data for select attributes
                foreach ($select_attributes as $select_attribute) {
                    if (array_key_exists($select_attribute, $attribute_record_data)) {
                        $select_attribute_values[$select_attribute][$attribute_record_data[$select_attribute]] = $attribute_record_data[$select_attribute];
                    }
                }

                // update import stats
                if ($products->offsetExists($record['sku'])) {
                    ++$products_updated;
                } else {
                    ++$products_added;
                    $products->offsetSet($record['sku'], $product);
                }
            } catch (\Exception $e) {
                $this->_flashAlert('danger', 'Issue importing ' . $record['sku'] . ' - ' . $e->getMessage() . ' [' . $e->getFile() . ':' . $e->getLine() . ']');
            }

        }

        // save related (must be done after products have been saved to make sure they have ids)
        $relatedFields = ['related', 'up_sell', 'cross_sell'];
        foreach ($productRecords as $record) {
            try {
                if ($products->offsetExists($record['sku'])) {
                    $product = $products->offsetGet($record['sku']);
                    $relatedProductSync = [];
                    foreach ($relatedFields as $relatedField) {
                        if (!empty($record[$relatedField])) {
                            $relatedSkus = explode(',', $record[$relatedField]);
                            foreach ($relatedSkus as $relatedSku) {
                                if (!$products->offsetExists($relatedSku)) {
                                    throw new \Exception('SKU ' . $relatedSku . ' not found');
                                }
                                $relatedProductSync[$products->offsetGet($relatedSku)->id][$relatedField] = 1;
                            }
                        }
                    }
                    $product->relatedProducts()->sync($relatedProductSync);
                }
            } catch (\Exception $e) {
                $this->_flashAlert('warning', 'Issue importing related items for ' . $record['sku'] . ' - ' . $e->getMessage() . ' [' . $e->getFile() . ':' . $e->getLine() . ']');
            }
        }

        // import variants
        $variationAttributes = [];
        foreach ($variantRecords as $variantRecord) {

            $product = $products->offsetExists($variantRecord['parent_sku']) ? $products->offsetGet($variantRecord['parent_sku']) : null;

            try {

                if (!$product) {
                    throw new \Exception('parent_sku invalid');
                }
                $product->variation_attributes = $product->variation_attributes ?: []; // convert null to array

                // convert variation conf to array  (from name field)
                $variantRecord['variation'] = [];
                $variationConf =  explode(',', $variantRecord['name']);
                foreach ($variationConf as $variationKeyValue) {
                    if (strpos($variationKeyValue, ':') !== false) {
                        list($variationAttributeKey, $variationAttributeValue) = explode(':', $variationKeyValue, 2);
                        if ($variationAttributeKey) {
                            $variantRecord['variation'][$variationAttributeKey] = $variationAttributeValue;
                        }
                    }
                }
                if (!$variantRecord['variation']) {
                    throw new \Exception('invalid variation config');
                } elseif ($keyDiffs = array_diff_key($product->variation_attributes, $variantRecord['variation'])) {
                    throw new \Exception('missing required key(s) ' . implode(',', array_keys($keyDiffs)));
                }

                foreach ($variantRecord['variation'] as $attribute => $value) {
                    $variationAttributes[$product->id][$attribute][] = $value; // for later when saving possible variation attribute options to product
                }

                // create new model or get existing
                $variant = new Product\Variation();
                foreach ($product->variations as $variation) {
                    /** @var Product\Variation $variation */
                    if ($variantRecord['variation'] ==  $variation->variationArray()) {
                        $variant = $variation;
                    }
                }

                // use images column for variation image
                if (array_key_exists('images', $variantRecord) && !array_key_exists('image', $variantRecord)) {
                    $variantRecord['image'] = $variantRecord['images'];
                }

                // saved configured variation
                $variantRecord = array_filter($variantRecord, function ($value) {
                    return $value !== ''; // remove blank values
                });
                $variantData = array_intersect_key($variantRecord, $variant_codes);
                $variantData['product_id'] = $product->id;
                $variantData['variation'] = json_encode($variantData['variation']);
                $variantData['image'] = array_key_exists('image', $variantData) ? $this->_importMedia($variantData['image'], 'images', $product, true) : null;
                $variant->forceFill($variantData)->save();

                // update import stats
                if ($variant->wasRecentlyCreated) {
                    ++$variants_added;
                } else {
                    ++$variants_updated;
                }
            } catch (\Exception $e) {
                $this->_flashAlert('danger', 'Issue importing variation ' . $variantRecord['parent_sku'] . ' - ' . $e->getMessage() . ' [' . $e->getFile() . ':' . $e->getLine() . ']');
            }

        }
        foreach ($variationAttributes as $productId => $productVariationAttributes) {
            // save variation attribute data
            $updatedProductVariationData = false;
            $product = $products->where('id', $productId)->first();
            $variation_attributes = $product->variation_attributes;
            foreach ($productVariationAttributes as $attribute => $values) {
                if (!array_key_exists($attribute, $variation_attributes)) {
                    $variation_attributes[$attribute] = [];
                }
                foreach ($values as $value) {
                    if (!array_key_exists($value, $variation_attributes[$attribute])) {
                        $variation_attributes[$attribute][$value] = ['display' => null];
                        $updatedProductVariationData = true;
                    }
                }
            }
            if ($updatedProductVariationData) {
                $product->variation_attributes = $variation_attributes;
                $product->save(['reindex' => false]);
            }
        }

        $this->_saveNewSelectOptions($product_attributes, $select_attribute_values);

        (new SearchIndex())->reindexAll();

        count($products) > 0 ? $this->_flashAlert('success', $products_added.' products added and '.$products_updated.' products updated successfully!') 
                             : $this->_flashAlert('danger', 'No products were imported.');

        if ($variants_added || $variants_updated) {
            $this->_flashAlert('success', $variants_added.' variations added and '.$variants_updated.' variations updated successfully!');
        }

        return redirect()->back();
    }

    /**
     * @param string $mediaString
     * @param string $attribute
     * @param Product $product
     * @param bool $variant
     * @return Product\Attribute\Model\FileModel\FileValue|string|null
     */
    protected function _importMedia($mediaString, $attribute, $product, $variant = false)
    {
        $relocatedMedia = [];
        $mediaItems = explode(',', $mediaString);
        $importedMediaPath = public_path('/uploads/catalogue/product/' . $product->id);
        if (!file_exists($importedMediaPath)) {
            mkdir($importedMediaPath, 0777, true);
        }
        if ($variant) {
            // variation file (will likely be images attribute)
            if ($mediaItems && $mediaItems[0] && $fileValue = $product->$attribute) {
                $sourceFile = new File(base_path($this->_mediaImportLocation) . $mediaItems[0]);
                return '/uploads/catalogue/product/' . $product->id . '/' . $sourceFile->getBasename();
            }
            return null;
        } else {
            // product file
            $mediaItems = array_unique($mediaItems);
            foreach ($mediaItems as $mediaItem) {
                $sourceMedia = base_path($this->_mediaImportLocation) . $mediaItem;
                if (\File::exists($sourceMedia)) {
                    $file = new File($sourceMedia);
                    $importedMedia = $importedMediaPath . '/' . $file->getBasename();
                    \File::copy($sourceMedia, $importedMedia);
                    $relocatedMedia[] = $importedMedia;
                } else {
                    $this->_flashAlert('warning', 'Media file not found for product id [' . $product->id . ']: ' . $sourceMedia);
                }
            }
            return $relocatedMedia ?
                (new Product\Attribute\Model\FileModel\FileValue())->setFiles($relocatedMedia) :
                null;
        }
    }

    /**
     * @param Product $product
     * @param string $categories
     */
    protected function _importCategories($product, $categories)
    {
        $categories = explode(',,', $categories);
        $category_ids = [];
        
        foreach ($categories as $category) {
            $category_ids[] = $this->_getCategoryId($category);
        }

        $product->categories()->sync($category_ids);

        /** @var CatalogueUrls $catalogueUrls */
        $catalogueUrls = app('coaster-commerce.catalog-urls');
        $catalogueUrls->setProductCategories($product->id, $categories);
    }

    /**
     * @param string $category_name
     * @return int
     */
    protected function _getCategoryId($category_name)
    {
        $path = [];
        $category_name_parts = explode('//', $category_name);
        foreach ($category_name_parts as $category_name_part) {
            $categoryId = $this->_getCategoryIdByName($category_name_part, $path);
            if (!$categoryId) {
                $category = new Category;
                $category->forceFill([
                    'name' => $category_name_part,
                    'path' => implode('/', $path) ?: null,
                    'anchor' => 1
                ])->save();
                static::$categoryNames[$category->name . '#@#' . $category->path] = $category->id;
                /** @var CatalogueUrls $catalogueUrls */
                $catalogueUrls = app('coaster-commerce.catalog-urls');
                if (end($path)) {
                    $catalogueUrls->addSubCatId(end($path), $category->id);
                }
                $catalogueUrls->setAnchor($category->id, $category->anchor);
                $categoryId = $category->id;
            }
            $path[] = $categoryId;
        }
        return $categoryId;
    }

    /**
     * @param string $name
     * @param array $path
     * @return int
     */
    protected function _getCategoryIdByName($name, $path = [])
    {
        if (is_null(static::$categoryNames)) {
            $categories = Category::select(['id', 'name', 'path'])->get();
            static::$categoryNames = [];
            foreach ($categories as $category) {
                static::$categoryNames[$category->name . '#@#' . $category->path] = $category->id;
            }
        }
        $key = $name . '#@#'. implode('/', $path);
        return array_key_exists($key, static::$categoryNames) ? static::$categoryNames[$key] : null;
    }

    /**
     * @param Product\Attribute[]|Collection $productAttributes
     * @param array $selectAttributeValues
     */
    protected function _saveNewSelectOptions($productAttributes, $selectAttributeValues)
    {
        // loop through all attributes with imported values
        foreach ($selectAttributeValues as $attribute => $importedValues) {
            // load current selectable values from database
            $selectAttribute = $productAttributes->where('code', $attribute)->first();
            if ($selectAttribute->meta->where('key', 'source')->first()) {
                continue; // ignore select attributes using a source class
            }
            $selectMetaOptions = $selectAttribute->meta->where('key', 'options')->first() ?: new Product\Attribute\Meta();
            $currentValues = [];
            $currentOptions = $selectMetaOptions->value ? json_decode($selectMetaOptions->value) : [];
            foreach ($currentOptions as $currentOption) {
                $currentValues[] = $currentOption->value;
            }
            // check if any imported values are not in database and add
            if ($newValues = array_diff($importedValues, $currentValues)) {
                foreach ($newValues as $newValue) {
                    $currentOptions[] = [
                        'name' => $newValue,
                        'value' => $newValue
                    ];
                }
                // save attribute options
                $selectMetaOptions->key = 'options';
                $selectMetaOptions->value = json_encode(array_values($currentOptions));
                $selectAttribute->meta()->save($selectMetaOptions);
            }
        }
    }

    /**
     * @return View
     */
    public function products()
    {
        $this->_setTitle('Product Import');
        return $this->_view('import.products', [
            'defaultAttributes' => array_diff(Product\Attribute::where('type', 'default')->get()->pluck('code')->toArray(), ['id', 'created_at', 'updated_at', 'url_key']),
            'optionalAttributes' => array_merge(['id', 'category', 'url_key', 'related', 'up_sell', 'cross_sell'], Product\Attribute::where('type', '=', 'eav')->get()->pluck('code')->toArray())
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadCategories(Request $request)
    {
        $path = $request->file('import-csv');
        $csvRecords = $path ? Reader::createFromPath($path)->setHeaderOffset(0) : [];

        $productUrls = (new Product)->newModelQuery()->pluck('url_key')->toArray();
        $fields = ['content', 'url_key', 'meta_title', 'meta_description', 'meta_keywords', 'position'];
        $categories = Category::all()->keyBy('id');

        $categories_added = 0;
        $categories_updated = 0;
        foreach ($csvRecords as $record) {
            $id = $this->_getCategoryId($record['name']);
            if (in_array($record['url_key'], $productUrls)) {
                $this->_flashAlert('warning', 'Possible product url conflict for "' . $record['url_key'] . '" on category id ' . $id);
            }
            /** @var Category $category */
            $category = $categories->offsetExists($id) ? $categories->offsetGet($id) : Category::find($id);
            $category->forceFill(array_intersect_key($record, array_fill_keys($fields, null)))->save();
            if ($categories->offsetExists($id)) {
                $categories_updated++;
            } else {
                $categories->offsetSet($id, $category);
                $categories_added++;
            }
        }

        $categories->count() > 0 ? $this->_flashAlert('success', $categories_added.' categories added and '.$categories_updated.' categories updated successfully!')
            : $this->_flashAlert('danger', 'No categories were imported.');

        return redirect()->back();
    }

    /**
     * @return View
     */
    public function categories()
    {
        $this->_setTitle('Category Import');
        return $this->_view('import.categories', [
            'defaultAttributes' => ['name'],
            'optionalAttributes' => ['content', 'url_key', 'meta_title', 'meta_description', 'meta_keywords', 'position']
        ]);
    }

}
