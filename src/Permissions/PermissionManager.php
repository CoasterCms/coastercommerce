<?php

namespace CoasterCommerce\Core\Permissions;

use CoasterCommerce\Core\Model\Permission;
use Illuminate\Contracts\Auth\UserProvider;

class PermissionManager
{

    /**
     * @var UserProvider
     */
    protected $_defaultUser;

    /**
     * @var array
     */
    protected $_actionIdsByName;

    /**
     * @var array
     */
    protected $_roleIdsByRole;

    /**
     * @var array
     */
    protected $_roleIdsByUser;

    /**
     * @var array
     */
    protected $_unprotectedRoutes;

    /**
     * PermissionManager constructor.
     * @param UserProvider $defaultUser
     */
    public function __construct($defaultUser = null)
    {
        $this->_defaultUser = $defaultUser;
        $this->_loadActions();
        $this->_loadRoles();
        $this->_setUnprotectedRotues();
    }

    /**
     * Set unprotected routes (must still be admin though)
     */
    protected function _setUnprotectedRotues()
    {
        $this->_unprotectedRoutes = [
            'coaster-commerce.admin.dashboard',
            'coaster-commerce.api' // TODO (any admin can currently access, though no update actions)
        ];
    }

    /**
     *
     */
    protected function _loadActions()
    {
        $this->_actionIdsByName = Permission\Action::pluck('id', 'action')->toArray();
    }

    /**
     *
     */
    protected function _loadRoles()
    {
        $roles = Permission\Role::all();
        $this->_roleIdsByRole = $roles->pluck('id', 'role_id')->toArray();
        $this->_roleIdsByUser = $roles->pluck('id', 'user_id')->toArray();
    }

    /**
     * @param string $actionName
     * @param UserProvider $user
     * @return bool
     */
    public function hasPermission($actionName, $user = null)
    {
        if(!($user = $user ?: $this->_defaultUser)) {
            return false;
        }
        if ($this->_isSuperAdmin($user) || $this->_isUnprotectedRoute($actionName)) {
            return true;
        }
        return (($roleId = $this->_computeRoleId($user)) && ($actionId = $this->_computeActionId($actionName))) ?
            Permission\Permission::where('role_id', $roleId)->where('action_id', $actionId)->exists() :
            false;
    }

    /**
     * @param UserProvider $user
     * @return bool
     */
    protected function _isSuperAdmin($user)
    {
        return $user->role && $user->role->admin == 2; // coaster cms super admin
    }

    /**
     * @param string $actionName
     * @return bool
     */
    protected function _isUnprotectedRoute($actionName)
    {
        foreach ($this->_actionMatches($actionName) as $actionNameMatch) {
            if (in_array($actionNameMatch, $this->_unprotectedRoutes)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return ecomm role_id (resolves from user->id or user->role_id)
     * @param UserProvider $user
     * @return integer|null
     */
    protected function _computeRoleId($user)
    {
        if (array_key_exists($user->id, $this->_roleIdsByUser)) {
            return $this->_roleIdsByUser[$user->id];
        }
        if (array_key_exists($user->role_id, $this->_roleIdsByRole)) {
            return $this->_roleIdsByRole[$user->role_id];
        }
        return null;
    }

    /**
     * Return ecomm action_id
     * @param string $actionName
     * @return array
     */
    protected function _computeActionId($actionName)
    {
        foreach ($this->_actionMatches($actionName) as $actionNameMatch) {
            if (array_key_exists($actionNameMatch, $this->_actionIdsByName)) {
                return $this->_actionIdsByName[$actionNameMatch];
            }
        }
        return false;
    }

    /**
     * Return possible action names (least generic => most generic)
     * @param string $actionName
     * @return array
     */
    protected function _actionMatches($actionName)
    {
        $matches = [];
        $actionParts = explode('.', $actionName);
        $actionPartCount = count($actionParts);
        for ($i = $actionPartCount; $i >= 1; $i--) {
            $matches[] = implode('.', array_slice($actionParts, 0, $i));
        }
        return $matches;
    }

}
