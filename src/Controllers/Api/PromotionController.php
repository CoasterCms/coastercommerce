<?php
namespace CoasterCommerce\Core\Controllers\Api;

use CoasterCommerce\Core\Model\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Exception;

class PromotionController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function getAdminList(Request $request)
    {
        $promotionColumns = [];
        $promotions = Promotion::with('customers')->get();
        foreach ($promotions as $promotion) {
            /** @var Promotion $promotion */
            $customers = $promotion->customers->pluck('email')->toArray();
            $customerGroups = $promotion->customerGroups->pluck('name')->toArray();
            $promotionColumns[] = [
                'id' => $promotion->id,
                'enabled' => $promotion->enabled ? 'Yes' : 'No',
                'type' => ucwords($promotion->type),
                'active' => $promotion->activeDateText(),
                'name' => $promotion->name,
                'customer' => implode(', ', array_merge($customers, $customerGroups)) ?: '*',
                'discount' => $promotion->discount_amount . ($promotion->discount_type == 'fixed' ? ' (Fixed)' : '%'),
                'edit' => '<a href="'.route('coaster-commerce.admin.promotion.edit', ['id' => $promotion->id]).'">Edit</a>',
            ];
        }
        return response()->json(['data' => $promotionColumns]);
    }

}
