<?php

namespace CoasterCommerce\Core\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

class Redirect extends Model
{

    public $table = 'cc_redirects';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->select(['id', 'name', 'url_key']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * @return string
     */
    public function redirectsTo()
    {
        if ($this->product) {
            return 'Product: ' . $this->product->name;
        } elseif ($this->category) {
            return 'Category: ' . $this->category->name;
        }
        return '';
    }

    /**
     * @return RedirectResponse
     */
    public function response()
    {
        if ($this->product_id) {
            $url = $this->product->getUrl();
        } else if ($this->category_id) {
            $url = $this->category->getUrl();
        } else {
            $url = '/';
        }
        return redirect($url, 301);
    }

}
