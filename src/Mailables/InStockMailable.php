<?php

namespace CoasterCommerce\Core\Mailables;

use CoasterCommerce\Core\Model\Product;

class InStockMailable extends AbstractMailable
{

    /**
     * @var string
     */
    protected $products;

    /**
     * @var string
     */
    protected $email;

    /**
     * Create a new message instance.
     *
     * @param string $email
     * @param array $product_ids
     * @return void
     */
    public function __construct($email, $product_ids)
    {
        $this->email = $email;
        $this->products = Product::whereIn('id', $product_ids)->get();
    }

    /**
     * @return static
     */
    public function build()
    {
        return $this
            ->to($this->email)
            ->markdown('coaster-commerce::emails.templates.in-stock', [
                'products' => $this->products
            ]);
    }

    /**
     * @return array
     */
    public static function testData()
    {
        return ['test@exmaple.com', [5]];
    }

}
