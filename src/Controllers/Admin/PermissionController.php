<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\Permission\Action;
use CoasterCommerce\Core\Model\Permission\Permission;
use CoasterCommerce\Core\Model\Permission\Role;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PermissionController extends AbstractController
{
    use ValidatesInput;

    /**
     * @return View
     */
    public function list()
    {
        $this->_setTitle('Permissions');
        return $this->_view('permission.list', [
            'roles' => Role::all(),
        ]);
    }

    /**
     * @return View
     */
    public function add()
    {
        $this->_setTitle('New Permissions Role');
        return $this->_editView(new Role());
    }

    /**
     * @param int $id
     * @return View
     */
    public function edit($id)
    {
        $this->_setTitle('Edit Permissions Role');
        if (!$role = Role::find($id)) {
            return $this->_notFoundView();
        }
        return $this->_editView($role);
    }

    /**
     * @param Role $role
     * @return View
     */
    protected function _editView($role)
    {
        return $this->_view('permission.edit', [
            'role' => $role,
            'actions' => Action::all(),
            'permissions' => Permission::where('role_id', $role->id)->pluck('action_id', 'action_id')->toArray(),
            'userOptions' => [null => '-- None --'] + call_user_func([Role::$userClass, 'pluck'], 'email', 'id')->toArray(),
            'roleOptions' => [null => '-- None --'] + call_user_func([Role::$roleClass, 'where'], 'admin', 1)->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return RedirectResponse|View
     * @throws ValidationException
     */
    public function save(Request $request, $id)
    {
        if ($id) {
            if (!$role = Role::find($id)) {
                return $this->_notFoundView();
            }
        } else {
            $role = new Role();
        }
        $inputData = $request->post('attributes');
        // validate attribute inputData
        $rules = [];
        $niceNames = [];
        foreach (['label'] as $attribute) {
            $rules['attributes.' . $attribute] = 'required';
            $niceNames['attributes.' . $attribute] = strtolower($attribute);
        }
        $this->validate(['attributes' => $inputData], $rules, [], $niceNames);
        // save inputData to role model
        $role
            ->forceFill(array_intersect_key($inputData, array_fill_keys(['label', 'role_id', 'user_id'], null)))
            ->save();
        // save permissions
        $actionIds = array_keys(array_filter($request->post('permission', [])));
        (new Permission())->newQuery()->where('role_id', $role->id)->whereNotIn('action_id', $actionIds)->delete();
        $newActionIds = array_diff($actionIds, $role->permissions()->pluck('id')->toArray());
        foreach ($newActionIds as $newActionId) {
            (new Permission())->newQuery()->insert([
                'role_id' => $role->id,
                'action_id' => $newActionId,
            ]);
        }
        // redirect based on save action
        $this->_flashAlert('success', 'Role "' . $role->label . '" saved!');
        return $request->post('saveAction') == 'continue' ?
            $this->_redirectRoute('permission.edit', ['id' => $role->id]) :
            $this->_redirectRoute('permission.list');
    }

    /**
     * @param int $id
     * @return RedirectResponse
     */
    public function delete($id)
    {
        if ($role = Role::find($id)) {
            if ($role->delete()) {
                $this->_flashAlert('success', 'Role "' . $role->label . '" deleted!');
            }
        }
        return $this->_redirectRoute('permission.list');
    }


}
