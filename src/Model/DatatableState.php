<?php

namespace CoasterCommerce\Core\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DatatableState extends Model
{

    /**
     * @var string
     */
    protected $table = 'cc_datatable_states';

    /**
     * @var array
     */
    protected static $_loadedStates = [];

    /**
     * @param string $name
     * @param array $data
     * @return static|null
     */
    public static function saveUserState($name, $data)
    {
        $user = Auth::guard()->user();
        $email = $user ? $user->email : null;
        if ($email) {
            DatatableState::unguard();
            $tableState = (new DatatableState())->newQuery()->updateOrCreate([
                'email' => $email,
                'name' => $name,
            ], $data);
        }
        return $tableState ?? null;
    }

    /**
     * @param string $name
     * @param bool $force
     * @return static|null
     */
    public static function loadUserState($name, $force = false)
    {
        $stateKey = null;
        $user = Auth::guard()->user();
        $email = $user ? $user->email : null;
        if ($email) {
            $stateKey = $email . '_' . $name;
            if (!array_key_exists($stateKey, static::$_loadedStates) || $force) {
                if ($tableState = (new static)->newQuery()->where('email', $email)->where('name', $name)->first()) {
                    static::$_loadedStates[$stateKey] = $tableState;
                }
            }
        }
        return array_key_exists($stateKey, static::$_loadedStates) ? static::$_loadedStates[$stateKey] : null;
    }

    /**
     * @param string $field
     * @return mixed
     */
    public function filterState($field)
    {
        $filterData = $this->filter_state ? json_decode($this->filter_state, true) : [];
        return array_key_exists($field, $filterData) ? $filterData[$field] : null;
    }

}
