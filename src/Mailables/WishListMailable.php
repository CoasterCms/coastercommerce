<?php

namespace CoasterCommerce\Core\Mailables;

use CoasterCommerce\Core\Model\Customer\WishList;
use CoasterCommerce\Core\Model\Product;

class WishListMailable extends AbstractMailable
{

    /**
     * @var WishList
     */
    protected $_wishList;

    /**
     * @var array
     */
    protected $_shareFormData;

    /**
     * Create a new message instance.
     *
     * @param WishList $wishList
     * @param array $shareFormData
     * @return void
     */
    public function __construct($wishList, $shareFormData)
    {
        $this->_wishList = $wishList;
        $this->_shareFormData = $shareFormData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->bcc($this->_shareFormData['emails'])
            ->markdown('coaster-commerce::emails.templates.customer-wishlist', [
                'wishList' => $this->_wishList,
                'shareFormData' => $this->_shareFormData
            ]);
    }

    /**
     * @return array
     */
    public static function testData()
    {
        $wishList = (new WishList())->forceFill([
            'name' => 'Test List'
        ]);
        $item1 = (new WishList\Item())->forceFill([
            'name' => 'Test List'
        ]);
        $product1 = (new Product())->forceFill([
            'name' => 'Top Hat',
            'sku' => 'THAT',
            'price' => 50.00
        ]);
        $variation1 = (new Product\Variation())->forceFill([
            'variation' => json_encode(['Size' => 'Average']),
        ]);
        $item1->setRelation('product', $product1)->setRelation('variation', $variation1);
        $item2 = (new WishList\Item())->forceFill([
            'name' => 'Test List'
        ]);
        $product2 = (new Product())->forceFill([
            'name' => 'The Best Item',
            'sku' => 'P-123456',
            'price' => 6.50
        ]);
        $variation2 = (new Product\Variation())->forceFill([
            'variation' => json_encode(['Colour' => 'Persimmon', 'Arms' => '3']),
        ]);
        $item2->setRelation('product', $product2)->setRelation('variation', $variation2);
        $item3 = (new WishList\Item())->forceFill([
            'name' => 'Test List'
        ]);
        $product3 = (new Product())->forceFill([
            'name' => 'iWallet',
            'sku' => 'P-WALLET',
            'price' => 9999.99
        ]);
        $item3->setRelation('product', $product3);
        $wishList->setRelation('items', collect([$item1, $item2, $item3]));
        $shareFormData = [
            'emails' => ['test@test.com'],
            'name' => 'Santa',
            'message' => 'I\'m sharing my list with you.'
        ];
        return [$wishList, $shareFormData];
    }

}
