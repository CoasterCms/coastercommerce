<?php
namespace CoasterCommerce\Core\Controllers\Api;

use CoasterCommerce\Core\Model\DatatableState;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Exception;

class DatatableStateController extends Controller
{

    /**
     * @var AuthManager
     */
    protected $_auth;

    /**
     * Auth constructor.
     * @param AuthManager $auth
     */
    public function __construct(AuthManager $auth)
    {
        $this->_auth = $auth;
    }

    /**
     * @param string $name
     * @return JsonResponse
     * @throws Exception
     */
    public function loadState($name)
    {
        $stateData = DatatableState::loadUserState($name);
        $tableState = ($stateData && $stateData->table_state) ? $stateData->table_state : '{}';
        return response()->json(['data' => $tableState]);
    }

    /**
     * @param Request $request
     * @param string $name
     * @return JsonResponse
     * @throws Exception
     */
    public function saveState(Request $request, $name)
    {
        $tableState = DatatableState::saveUserState($name, ['table_state' => $request->post('value')]);
        return response()->json(['data' => $tableState ? 'ok' : 'error']);
    }

}
