<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Model\Category;
use CoasterCommerce\Core\Model\CategoryProducts;
use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Redirect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProductRedirectController extends AbstractController
{

    /**
     * @param Request $request
     * @return View
     */
    public function select(Request $request)
    {
        $idString = $request->input('ids');
        $ids = $idString ? array_map('intval', explode(',', $idString)) : [];
        return $this->_select($ids);
    }

    /**
     * @param string $id
     * @return View
     */
    public function selectSingle($id)
    {
        return $this->_select([intval($id)]);
    }

    /**
     * @param array $ids
     * @return View
     */
    protected function _select($ids)
    {
        $products = (new Product())->newModelQuery()
            ->whereIn('id', $ids)
            ->get();
        return $this->_view('product.redirect.select', [
            'redirectProducts' => $products,
            'productOptions' => array_diff_key((new Product\Attribute\OptionSource\Product)->optionsData(), array_fill_keys($ids, null)),
            'categoryOptions' => (new Product\Attribute\OptionSource\Category)->optionsData()
        ]);
    }

    /**
     * @param Request $request
     * @return View
     */
    public function apply(Request $request)
    {
        $redirects = $request->post('redirect', []);
        // load product and category data which will be used for getting urls
        $products = (new Product())->newModelQuery()->whereIn('id', array_keys($redirects))->get()->keyBy('id');
        $categoryIds = [];
        $productCategories = [];
        $productCategoriesCollection = (new CategoryProducts())->newModelQuery()->whereIn('product_id', array_keys($redirects))->get();
        foreach ($productCategoriesCollection as $productCategory) {
            $productCategories[$productCategory->product_id][] = $productCategory->category_id;
            $categoryIds[] = $productCategory->category_id;
        }
        $categories = (new Category())->newModelQuery()->whereIn('id', array_unique($categoryIds))->get()->keyBy('id');
        // load redirects to products that are being deleted
        $redirectsProductIdsToUpdate = (new Redirect())->newModelQuery()->whereIn('product_id', array_keys($redirects))
            ->groupBy('product_id')->pluck('product_id', 'product_id')->toArray();
        // create collection of each products urls with entity they are being redirect to
        $newRedirectsData = collect();
        foreach ($redirects as $productId => $redirect) {
            $redirectDetails = explode(':', $redirect);
            $redirectToProductId = $redirectDetails[0] == 'p' ? $redirectDetails[1] : null;
            $redirectToCategoryId = $redirectDetails[0] == 'c' ? $redirectDetails[1] : null;
            if ($redirectToCategoryId || $redirectToProductId) {
                // get all urls for products
                $urls = [$products->offsetGet($productId)->getUrl()];
                if (array_key_exists($productId, $productCategories)) {
                    foreach ($productCategories[$productId] as $productCategoryId) {
                        $urls[] = $products->offsetGet($productId)->getUrl($categories->offsetGet($productCategoryId));
                    }
                }
                // add to collection
                foreach ($urls as $url) {
                    $newRedirectsData->push([
                        'url' => ltrim($url, '/'),
                        'product_id' => $redirectToProductId,
                        'category_id' => $redirectToCategoryId,
                    ]);
                }
                // delete existing redirect on product urls (shouldn't exist, but stops unique error on insert)
                Redirect::whereIn('url', $newRedirectsData->pluck('url')->toArray())->delete();
            }
            // update any redirects to products that are being deleted to prevent 404s
            if (array_key_exists($productId, $redirectsProductIdsToUpdate)) {
                $updateQuery = Redirect::where('product_id', $productId);
                if ($redirectToCategoryId || $redirectToProductId) {
                    $updateQuery->update([
                        'product_id' => $redirectToProductId,
                        'category_id' => $redirectToCategoryId,
                    ]);
                } else {
                    $updateQuery->delete();
                }
            }
        }
        // add new redirects
        $newRedirectsData->chunk(100)->each(function ($redirectData) {
            Redirect::insert($redirectData->toArray());
        });
        (new Product())->newModelQuery()
            ->whereIn('id', array_keys($redirects))
            ->delete();
        return $this->_view('product.redirect.complete', [
            'redirectedProducts' => count($redirects)
        ]);
    }

}
