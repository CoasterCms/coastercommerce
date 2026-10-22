<?php
namespace CoasterCommerce\Core\Controllers\Api;

use CoasterCommerce\Core\Model\DatatableState;
use CoasterCommerce\Core\Model\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Exception;

class ProductController extends Controller
{

    /**
     * Admin product list data.
     *
     * With DataTables server-side parameters (draw/start/length/order/search) only the requested page is
     * loaded and formatted; with ids_only=1 the ids of every matching product are returned (for "select
     * all" across pages); with neither, every product is returned as before.
     *
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
        $productQuery = (new Product)->newQuery();
        if ($filters = $request->input('attributes')) {
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
        }
        DatatableState::saveUserState('product_list', ['filter_state' => json_encode($filters ?: [])]); // saving filters

        $serverSide = $request->has('draw');
        if ($serverSide || $request->boolean('ids_only')) {
            $this->_applySearch($productQuery, trim((string) $request->input('search.value')));
        }
        if ($request->boolean('ids_only')) {
            return response()->json(['ids' => $productQuery->pluck('cc_products.id')->map(function ($id) {
                return (int) $id;
            })->values()]);
        }
        if (!$serverSide) {
            $products = $productQuery->with(['variations', 'categories'])->get($adminColumnCodes)->keyBy('id');
            return response()->json(['data' => $this->_formatRows($products, $attributes, $adminColumnCodes)]);
        }

        $recordsFiltered = (clone $productQuery)->count();
        $this->_applyOrder($productQuery, $request, $columnAttributes);
        $length = (int) $request->input('length', 25);
        $productQuery->skip(max((int) $request->input('start', 0), 0));
        if ($length > 0) {
            $productQuery->take(min($length, 1000));
        }
        $products = $productQuery->with(['variations', 'categories'])->get($adminColumnCodes)->keyBy('id');
        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => (new Product)->newQuery()->count(),
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->_formatRows($products, $attributes, $adminColumnCodes),
        ]);
    }

    /**
     * The list's search box: matches id, name, sku, a variation sku or brand
     * @param Builder $productQuery
     * @param string $term
     */
    protected function _applySearch($productQuery, $term)
    {
        if ($term === '') {
            return;
        }
        $like = '%' . addcslashes($term, '%_\\') . '%';
        $ids = (new Product)->newQuery()->where('name', 'like', $like)->orWhere('sku', 'like', $like)->pluck('id');
        $ids = $ids->merge(Product\Variation::where('sku', 'like', $like)->pluck('product_id'));
        if (Product\AttributeCache::getProductAttributes()->offsetExists('brand')) {
            $ids = $ids->merge((new Product)->newQuery()->where('brand', 'like', $like)->pluck('id'));
        }
        if (ctype_digit($term)) {
            $ids->push((int) $term);
        }
        $productQuery->whereIn('cc_products.id', $ids->unique()->values()->all());
    }

    /**
     * Sort on the requested column (product and eav attributes; virtual ones can't be sorted in SQL)
     * @param Builder $productQuery
     * @param Request $request
     * @param \Illuminate\Support\Collection $columnAttributes
     */
    protected function _applyOrder($productQuery, $request, $columnAttributes)
    {
        $columnIndex = $request->input('order.0.column');
        $code = $request->input('columns.' . $columnIndex . '.data');
        $direction = strtolower($request->input('order.0.dir')) === 'desc' ? 'desc' : 'asc';
        $attribute = $columnAttributes->get($code);
        $conf = $attribute ? $attribute->getDataTableColumnConf() : [];
        if ($attribute && ($conf['orderable'] ?? true)) {
            $productQuery->orderBy($code, $direction);
        }
        $productQuery->orderBy('cc_products.id', $direction); // stable paging
    }

    /**
     * @param \Illuminate\Support\Collection $products
     * @param \Illuminate\Support\Collection $attributes
     * @param array $adminColumnCodes
     * @return array
     */
    protected function _formatRows($products, $attributes, $adminColumnCodes)
    {
        $productAttributeData = $products->toAttributeArray();
        $responseData = [];
        foreach ($productAttributeData as $id => $productAttributes) {
            foreach ($adminColumnCodes as $attributeCode) {
                $responseData[$id][$attributeCode] = Product\AttributeCache::$frontendTypes->dataTableCellValue($attributes[$attributeCode], $productAttributes[$attributeCode], $id);
            }
            $responseData[$id]['search-data'] = implode(' ', array_filter($products->offsetGet($id)->variations->pluck('sku')->toArray()));
        }
        return array_values($responseData);
    }

}
