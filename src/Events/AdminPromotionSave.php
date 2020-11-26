<?php namespace CoasterCommerce\Core\Events;

use CoasterCommerce\Core\Model\Promotion;

class AdminPromotionSave
{

    /**
     * @var Promotion
     */
    public $promotion;

    /**
     * @var array
     */
    public $inputData;

    /**
     * AdminPromotionSave constructor.
     * @param Promotion $promotion
     * @param array $inputData
     */
    public function __construct(Promotion $promotion, array $inputData)
    {
        $this->promotion = $promotion;
        $this->inputData = $inputData;
    }

}

