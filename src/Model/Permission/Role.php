<?php

namespace CoasterCommerce\Core\Model\Permission;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    public $table = 'cc_permission_roles';

    public static $userClass = 'CoasterCms\Models\User';

    public static $roleClass = 'CoasterCms\Models\UserRole';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'role_id');
    }

    /**
     * @return string
     */
    public function usedFor()
    {
        if ($this->user_id) {
            $user = call_user_func([static::$userClass, 'find'], $this->user_id);
            return $user->email;
        }
        if ($this->role_id) {
            $role = call_user_func([static::$roleClass, 'find'], $this->role_id);
            return $role->name;
        }
        return '';
    }

}
