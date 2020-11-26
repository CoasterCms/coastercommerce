<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Model\Product;
use Illuminate\Database\Eloquent\Model;

class ProductFileController extends FileUploadController
{

    /**
     * @return string
     */
    protected function _getRequestField()
    {
        return 'attributes.' . $this->_getModelField();
    }

    /**
     * @param int $id
     * @return Product
     */
    protected function _getEntity($id)
    {
        return Product::find($id);
    }

    /**
     * @return string
     */
    protected function _getModelField()
    {
        return request()->post('code');
    }

    /**
     * @param Model $entity
     * @return string
     */
    protected function _getPublicSavePath($entity)
    {
        return '/uploads/catalogue/product/' . $entity->id;
    }

}
