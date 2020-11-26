<?php
namespace CoasterCommerce\Core\Controllers\Api;

use CoasterCommerce\Core\Model\Redirect;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Exception;

class RedirectController extends Controller
{

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function getAdminList()
    {
        $redirectColumns = [];
        $redirects = Redirect::with(['product', 'category'])->get();
        foreach ($redirects as $redirect) {
            /** @var Redirect $redirect */
            $redirectColumns[] = [
                'id' => $redirect->id,
                'url' => $redirect->url,
                'redirects_to' => $redirect->redirectsTo(),
            ];
        }
        return response()->json(['data' => $redirectColumns]);
    }

}
