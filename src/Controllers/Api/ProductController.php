<?php
namespace CoasterCommerce\Core\Controllers\Api;

use CoasterCommerce\Core\Model\DatatableState;
use CoasterCommerce\Core\Model\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Exception;

class ProductController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getAdminList(Request $request)
    {
        $attributes = Product\AttributeCache::getProductAttributes();
        $columnAttributes = Product\Attribute::getAdminColumns()->keyBy('code');
        $adminColumnCodes = $columnAttributes->pluck('code')->toArray();
        $adminColumnCodes = array_unique(array_merge($adminColumnCodes, ['id'])); // always need id (for edit link)
        if ($filters = $request->get('attributes')) {
            $productQuery = (new Product)->newQuery();
            foreach ($filters as $attributeCode => $filterValue) {
                if (!is_null($filterValue)) {
                    // if virtual let model deal with filter as it may be more customized otherwise let frontend class
                    if ($attributes[$attributeCode]->type == 'virtual') {
                        $productQuery = Product\AttributeCache::$modelTypes->filterQuery($attributes[$attributeCode], $filterValue, $productQuery);
                    } else {
                        $productQuery = Product\AttributeCache::$frontendTypes->filterQuery($attributes[$attributeCode], $filterValue, $productQuery);
                    }
                }
            }
        } else {
            $productQuery = new Product;
        }
        DatatableState::saveUserState('product_list', ['filter_state' => json_encode($filters)]); // saving filters
        $products = $productQuery->with(['variations', 'categories'])->get($adminColumnCodes)->keyBy('id');
        $productAttributeData = $products->toAttributeArray();
        $responseData = [];
        foreach ($productAttributeData as $id => $productAttributes) {
            foreach ($adminColumnCodes as $attributeCode) {
                $responseData[$id][$attributeCode] = Product\AttributeCache::$frontendTypes->dataTableCellValue($attributes[$attributeCode], $productAttributes[$attributeCode], $id);
            }
            $responseData[$id]['search-data'] = implode(' ', array_filter($products->offsetGet($id)->variations->pluck('sku')->toArray()));
        }
        return response()->json(['data' => array_values($responseData)]);
    }

}
