<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\CatalogueUrls\CatalogueUrls;
use CoasterCommerce\Core\Events\AdminProductSave;
use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Product\Attribute;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends AbstractController
{
    use ValidatesInput;

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
     * @return View
     */
    public function list()
    {
        $this->_setTitle('Products');
        return $this->_view('product.list', [
            'filterAttributes' => Attribute::getAdminFilters(),
            'columnConf' => Attribute::getDataTableColumnsConf(),
            'massActionsEnabled' => Attribute::hasMassUpdateAttributes()
        ]);
    }

    /**
     * @param string $productId
     * @return View
     */
    public function edit($productId)
    {
        if (!$product = Product::with('categories')->find($productId)) {
            return $this->_notFoundView();
        }
        $this->_setTitle('Editing ' . $product->name);
        $this->_view->share('product', $product);
        $groups = Attribute\Group::with('productAttributes')->orderBy('position')->get();
        return $this->_view('product.edit', [
            'groups' => $groups
        ]);
    }

    /**
     * @return View
     */
    public function add()
    {
        $this->_setTitle('New Product');
        $groups = Attribute\Group::with('productAttributes')->orderBy('position')->get();
        $this->_view->share('product', new Product());
        return $this->_view('product.edit', [
            'groups' => $groups
        ]);
    }

    /**
     * @param Request $request
     * @param int $productId
     * @return RedirectResponse|View
     * @throws ValidationException
     */
    public function save(Request $request, $productId)
    {
        // load or create product and attributes
        if ($productId) {
            if (!$product = Product::find($productId)) {
                return $this->_notFoundView();
            }
        } else {
            $product = new Product();
        }
        $attributes = Product\AttributeCache::getProductAttributes();
        // load inputData and run modifications based on frontend class
        // ignore fileInputs and these are handled via ajax
        $inputData = array_diff_key($request->post('attributes'), $request->post('fileInput', []));
        foreach ($inputData as $attributeCode => $value) {
            $inputData[$attributeCode] = Product\AttributeCache::$frontendTypes
                ->modifySubmittedData($attributes[$attributeCode], $value);
        }
        // add custom validation rule
        $urlKeyError = '';
        $categoryIds = array_key_exists('category_ids', $inputData) ? $inputData['category_ids'] : [];
        $this->getValidationFactory()->extend('prod_unique_url', function ($attribute, $value, $parameters) use($productId, $categoryIds, &$urlKeyError) {
            /** @var CatalogueUrls $catalogueUrls */
            $catalogueUrls = app('coaster-commerce.catalog-urls');
            $conflicts = $catalogueUrls->productUrlConflicts($productId, $value, $categoryIds);
            $urlKeyError = implode(', ', $conflicts);
            return !$conflicts;
        });
        // validate attribute inputData
        $rules = [];
        $niceNames = [];
        foreach ($attributes as $attribute) {
            $attribute->admin_validation = Product\AttributeCache::$frontendTypes
                ->submissionRules($attribute,  $attribute->admin_validation);
            if ($attribute->admin_validation) {
                // useful for unique rules
                $rules[$attribute->fieldKey()] = str_replace('[id]', $productId ? ',' . $productId : '', $attribute->admin_validation);
                $niceNames[$attribute->fieldKey()] = strtolower($attribute->name);
                if ($attribute->code == 'url_key') { // add customer validation rule to url_key field
                    $urlRules = explode('|', $rules[$attribute->fieldKey()]);
                    $urlRules[] = 'prod_unique_url';
                    $rules[$attribute->fieldKey()] = implode('|', $urlRules);
                }
            }
        }
        $this->validate(['attributes' => $inputData], $rules, ['prod_unique_url' => &$urlKeyError], $niceNames);
        // save inputData to product model
        $product
            ->forceFill($inputData)
            ->save();
        try {
            // save non product model data (ie. categories)
            event(new AdminProductSave($product, $inputData));
        } catch (ValidationException $e) {
            // fixes issues on additional validation with new products
            $e->redirectTo(route('coaster-commerce.admin.product.edit', ['id' => $product->id]));
            throw $e;
        }
        // redirect based on save action
        $this->_flashAlert('success', 'Product "' . $product->name . '" saved!');
        return $request->post('saveAction') == 'continue' ?
            $this->_redirectRoute('product.edit', ['id' => $product->id]) :
            $this->_redirectRoute('product.list');
    }

    /**
     * @param int $productId
     * @return RedirectResponse
     */
    public function delete($productId)
    {
        if ($product = Product::find($productId)) {
            if ($product->delete()) {
                $this->_flashAlert('success', 'Product "' . $product->name . '" deleted!');
            }
        }
        return $this->_redirectRoute('product.list');
    }

}
