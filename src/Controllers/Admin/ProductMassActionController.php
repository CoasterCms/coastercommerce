<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Events\AdminProductMassUpdate;
use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Product\Attribute;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;

class ProductMassActionController extends AbstractController
{
    use ValidatesInput;

    /**
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        switch ($request->post('action') ?: $request->old('action')) {
            case 'update':
                return $this->_updateReview($request);
            case 'delete':
                return $this->_deleteReview($request);
            default:
                return $this->_notFoundView();
        }
    }

    /**
     * @param string $idString
     * @return array
     */
    protected function _getProductIds($idString)
    {
        return array_map('intval', explode(',', $idString));
    }

    /**
     * @param string $idString
     * @return Product[]|Collection
     */
    protected function _getProducts($idString)
    {
        return (new Product())->newModelQuery()
            ->whereIn('id', $this->_getProductIds($idString))
            ->get();
    }

    /**
     * @param Request $request
     * @return View
     */
    protected function _updateReview(Request $request)
    {
        $ids = $request->post('ids') ?: $request->old('ids');
        if ($attributeSelection = $request->post('attribute_ids') ?: $request->old('attribute_ids')) {
            $step = 2;
            $massAttributes = Product\Attribute::whereIn('id', array_keys(array_filter($attributeSelection)))->get();
        } else {
            $step = 1;
            $massAttributes = Product\Attribute::where('admin_massupdate', 1)->get();
        }
        return $this->_view('product.mass-action.update-review', [
            'products' => $this->_getProducts($ids),
            'idString' => $ids,
            'step' => $step,
            'massAttributes' => $massAttributes
        ]);
    }

    /**
     * mass update should work with most attributes, except gallery or variation_attributes (and unique fields)
     *
     * @param Request $request
     * @return View
     * @throws ValidationException
     */
    public function updateComplete(Request $request)
    {
        $attributeIds = array_keys(array_filter($request->post('attribute_ids', [])));
        $attributes = (new Attribute)->with(['eav', 'meta'])->whereIn('id', $attributeIds)->get()->keyBy('code');
        // load inputData and run modifications based on frontend class
        // ignore fileInputs and these are handled via ajax
        $inputData = array_diff_key($request->post('attributes'), $request->post('fileInput', []));
        foreach ($inputData as $attributeCode => $value) {
            $inputData[$attributeCode] = Product\AttributeCache::$frontendTypes
                ->modifySubmittedData($attributes[$attributeCode], $value);
        }
        // validate attribute inputData
        $rules = [];
        $niceNames = [];
        foreach ($attributes as $attribute) {
            $attribute->admin_validation = Product\AttributeCache::$frontendTypes
                ->submissionRules($attribute,  $attribute->admin_validation);
            if ($attribute->admin_validation) {
                $rules[$attribute->fieldKey()] = $attribute->admin_validation;
                $niceNames[$attribute->fieldKey()] = strtolower($attribute->name);
            }
        }
        $this->validate(['attributes' => $inputData], $rules, [], $niceNames);

        // save update attributes to all selected products
        $products = $this->_getProducts($request->post('ids'));
        foreach ($products as $product) {
            $product
                ->forceFill($inputData)
                ->save(['reindex' => false]);
        }
        // save non product model data (ie. categories)
        event(new AdminProductMassUpdate($products, $inputData));
        // index all selected products afterwards (better performance for large lists of products)
        (new Product\SearchIndex())->reindexAll($this->_getProductIds($request->post('ids')));

        return $this->_view('product.mass-action.update-complete', [
            'updated' => $products->count()
        ]);
    }

    /**
     * @param Request $request
     * @return View
     */
    protected function _deleteReview(Request $request)
    {
        return $this->_view('product.mass-action.delete-review', [
            'products' => $this->_getProducts($request->post('ids')),
            'idString' => $request->post('ids')
        ]);
    }

    /**
     * @param Request $request
     * @return View
     */
    public function deleteComplete(Request $request)
    {
        try {
            $deleted = (new Product())->newModelQuery()
                ->whereIn('id', $this->_getProductIds($request->post('ids')))
                ->delete();
        } catch (\Exception $e) {
            $deleted = 0;
        }
        return $this->_view('product.mass-action.delete-complete', [
            'deleted' => $deleted
        ]);
    }

}
