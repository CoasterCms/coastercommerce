<?php

namespace CoasterCommerce\Core\Model\Tax;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxZone extends Model
{

    public $table = 'cc_tax_zones';

    /**
     * @return HasMany
     */
    public function areas()
    {
        return $this->hasMany(TaxZoneArea::class);
    }

}
