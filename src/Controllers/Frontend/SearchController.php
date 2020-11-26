<?php
namespace CoasterCommerce\Core\Controllers\Frontend;

use CoasterCommerce\Core\Model\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Product\AttributeCache;
use CoasterCommerce\Core\Model\Product\SearchIndex;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class SearchController extends AbstractController
{


    /**
     * @param Request $request
     * @param string $searchTerm
     * @return View
     */
    public function results(Request $request, $searchTerm = null)
    {
        $searchTerm = $searchTerm ?: $request->get('q');

        if ($searchTerm) {
            $productWeights = (new SearchIndex())->findTermProductWeights($searchTerm);
            $productIds = array_map(function ($productWeight) {
                return $productWeight->product_id;
            }, $productWeights);
        } else {
            $productIds = (new Product)->newModelQuery()->pluck('id')->toArray();
        }

        $productIds = (new SearchIndex())
            ->filterResults($productIds, request()->query());

        $orderBy = $request->get('o', 'relevance');
        $orderBy = AttributeCache::getProductAttributes()->offsetExists($orderBy) ? $orderBy : 'relevance';
        $dir = $request->get('d', 'asc');
        $dir = in_array($dir, ['asc', 'desc']) ? $dir : 'asc';

        $pageSize = Setting::getValue('catalogue_pagination') ?: 30;
        /** @var Builder $productsQuery */
        $productsQuery = (new Product)->with(['variations', 'categories'])->where('enabled', 1);
        if ($orderBy == 'relevance') {
            $productIds = $dir == 'asc' ? $productIds : array_reverse($productIds);
            $productIdsSlice = array_slice($productIds, ($request->get('page', 1)-1) * $pageSize, $pageSize);
            /** @var Collection $products */
            $products = $productsQuery->whereIn('id', $productIdsSlice)->get()->keyBy('id');
            $orderedProducts = collect();
            foreach ($productIdsSlice as $productId) {
                if ($products->offsetExists($productId)) {
                    $orderedProducts->push($products->offsetGet($productId));
                }
            }
            $orderedProducts = (new LengthAwarePaginator($orderedProducts, count($productIds), $pageSize, null, [
                'path' => Paginator::resolveCurrentPath()
            ]))->appends($request->except('page'));
        } else {
            $orderedProducts = $productsQuery->whereIn('id', $productIds)->orderBy($orderBy, $dir)->paginate($pageSize);
        }

        $searchData = new \stdClass();
        $searchData->term = $searchTerm;
        $searchData->results = count($productIds);

        // load commerce vars and page data
        $this->_setPageMeta('Search');
        return $this->_view('search', [
            'matchingProductIds' => $productIds, // full list of ids
            'products' => $orderedProducts, // paginated models
            'catalogueSearch' => $searchData,
        ]);
    }

}
