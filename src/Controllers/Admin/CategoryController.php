<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\CatalogueUrls\CatalogueUrls;
use CoasterCommerce\Core\Events\AdminCategorySave;
use CoasterCommerce\Core\Events\CategoryRenderContentFields;
use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Category;
use CoasterCommerce\Core\Renderer\Admin\CategoryList;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Contracts\View\View;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CategoryController extends AbstractController
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
        $this->_setTitle('Categories');
        return $this->_view('category.list', [
            'listRenderer' => new CategoryList(null),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function move(Request $request)
    {
        $category = Category::find($request->post('id'));
        if ($category) {
            // save new path
            if ($request->post('parentId') && $parentCategory = Category::find($request->post('parentId'))) {
                /** @var CatalogueUrls $catalogueUrls */
                $catalogueUrls = app(CatalogueUrls::class);
                if ($urlConflicts = $catalogueUrls->categoryUrlConflicts($category->id, $category->url_key, $parentCategory->id)) {
                    return response()->json(['error' => implode(', ', $urlConflicts)], 500);
                }
                $category->path = $parentCategory->fullPath();
            } else {
                $category->path = null;
            }
            $category->save();
            // save new positions
            /** @var DatabaseManager $db */
            $db = app('db');
            $db->beginTransaction();
            foreach ($request->post('positions', []) as $k => $categoryId) {
                (new Category)->newQuery()->where('id', $categoryId)->update(['position' => ($k + 1) * 10]);
            }
            $db->commit();
            return response()->json([]);
        }
        return response()->json(['error' => 'Category no longer exists'], 500);
    }

    /**
     * @param int $categoryId
     * @return JsonResponse
     */
    public function deletePost($categoryId)
    {
        $this->delete($categoryId, false);
        return response()->json([]);
    }


    /**
     * @param int $categoryId
     * @param bool $alert
     * @return RedirectResponse
     */
    public function delete($categoryId, $alert = true)
    {
        $category = Category::find($categoryId);
        if ($category) {
            (new Category)->newQuery()->where('path', $category->fullPath())->delete();
            (new Category)->newQuery()->where('path', 'LIKE', $category->fullPath() . '/%')->delete();
            if ($category->delete() && $alert) {
                $this->_flashAlert('success', 'Category "' . $category->name . '" deleted!');
            }
        }
        return $this->_redirectRoute('category.list');
    }

    /**
     * @return View
     */
    public function add()
    {
        $this->_setTitle('New Category');
        $contentFieldsEvent = new CategoryRenderContentFields(new Category());
        event($contentFieldsEvent);
        return $this->_view('category.edit', [
            'category' => new Category(),
            'contentFieldsHtml' => $contentFieldsEvent->getHtml()
        ]);
    }

    /**
     * @param int $categoryId
     * @return View
     */
    public function edit($categoryId)
    {
        if (!$category = Category::find($categoryId)) {
            return $this->_notFoundView();
        }
        $this->_setTitle('Editing ' . $category->name);
        $contentFieldsEvent = new CategoryRenderContentFields($category);
        event($contentFieldsEvent);
        return $this->_view('category.edit', [
            'category' => $category,
            'contentFieldsHtml' => $contentFieldsEvent->getHtml()
        ]);
    }

    /**
     * @param Request $request
     * @param int $categoryId
     * @return RedirectResponse|View
     * @throws ValidationException
     */
    public function save(Request $request, $categoryId)
    {
        if ($categoryId) {
            if (!$category = Category::find($categoryId)) {
                return $this->_notFoundView();
            }
        } else {
            $category = new Category();
        }
        $parentCategory = null;
        $inputData = $request->post('attributes');
        if (!empty($inputData['path'])) {
            if ($parentCategory = Category::find($inputData['path'])) {
                $newPath = $parentCategory->fullPath();
            }
        }
        $inputData['path'] = isset($newPath) ? $newPath : null;
        unset($inputData['images']);

        $urlKeyError = '';
        $this->getValidationFactory()->extend('cat_unique_url', function ($attribute, $value, $parameters) use($categoryId, $parentCategory, &$urlKeyError) {
            /** @var CatalogueUrls $catalogueUrls */
            $catalogueUrls = app('coaster-commerce.catalog-urls');
            $conflicts = $catalogueUrls->categoryUrlConflicts($categoryId, $value, $parentCategory ? $parentCategory->id : null);
            $urlKeyError = implode(', ', $conflicts);
            return !$conflicts;
        });

        // validate attribute inputData
        $rules = [];
        $niceNames = [];
        foreach (['name', 'url_key'] as $attribute) {
            $rules['attributes.' . $attribute] = $attribute == 'url_key' ? 'required|cat_unique_url' : 'required';
            $niceNames['attributes.' . $attribute] = strtolower($attribute);
        }
        $this->validate(['attributes' => $inputData], $rules, ['cat_unique_url' => &$urlKeyError], $niceNames);
        // save inputData to category model
        $category
            ->forceFill(array_intersect_key($inputData, array_fill_keys(Schema::getColumnListing((new Category)->getTable()), null)))
            ->save();
        // save non category model data (ie. products)
        event(new AdminCategorySave($category, $inputData));
        // redirect based on save action
        $this->_flashAlert('success', 'Category "' . $category->name . '" saved!');
        return $request->post('saveAction') == 'continue' ?
            $this->_redirectRoute('category.edit', ['id' => $category->id]) :
            $this->_redirectRoute('category.list');
    }

}
