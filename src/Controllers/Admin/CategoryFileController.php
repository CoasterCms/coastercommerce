<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Model\Category;
use Illuminate\Database\Eloquent\Model;

class CategoryFileController extends FileUploadController
{

    /**
     * @return string
     */
    protected function _getModelField()
    {
        return 'images';
    }

    /**
     * @param int $id
     * @return Category
     */
    protected function _getEntity($id)
    {
        return Category::find($id);
    }

    /**
     * @param Model $entity
     * @return string
     */
    protected function _getPublicSavePath($entity)
    {
        return '/uploads/catalogue/category/' . $entity->id;
    }

}
