<?php
namespace CoasterCommerce\Core\Controllers\Api;

use CoasterCommerce\Core\Model\Product\Attribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Exception;

class AttributeController extends Controller
{

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function getAdminList()
    {
        $attributeColumns = [];
        $attributes = Attribute::with(['meta', 'eav'])->get();
        foreach ($attributes as $attribute) {
            /** @var Attribute $attribute */
            $attributeColumns[] = [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'code' => $attribute->code,
                'datatype' => $attribute->getDataType(),
                'admin_filter' => $attribute->admin_filter,
                'admin_column' => $attribute->admin_column,
                'search_weight' => $attribute->search_weight,
                'system' => $attribute->isSystem() ? 'Yes' : 'No',
                'edit' => '<a href="'.route('coaster-commerce.admin.attribute.edit', ['id' => $attribute->id]).'">Edit</a>'
            ];
        }
        return response()->json(['data' => $attributeColumns]);
    }

}
