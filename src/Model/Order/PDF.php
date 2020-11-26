<?php

namespace CoasterCommerce\Core\Model\Order;

use CoasterCommerce\Core\Currency\Format;
use CoasterCommerce\Core\Model\Order;
use CoasterCommerce\Core\Model\Setting;
use CoasterCommerce\Core\PDF\PDF as BasePDF;

class PDF
{

    /**
     * @var BasePDF
     */
    protected $_pdf;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * PDF constructor.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->_pdf = new BasePDF();
        $this->_order = $order;
    }

    /**
     * Adds all invoice sections to pdf
     */
    public function addInvoiceSections()
    {
        $this->addHeader();
        $this->_pdf->SetY($this->_pdf->GetY() + 5);
        $this->addNumberAndDate();
        $this->_pdf->SetY($this->_pdf->GetY() + 6);
        $this->addAddresses();
        $this->_pdf->SetY($this->_pdf->GetY() + 5);
        $this->addPaymentShippingMethods();
        $this->_pdf->SetY($this->_pdf->GetY() + 10);
        $this->addOrderItems();
        $this->_pdf->SetY($this->_pdf->GetY() + 5);
        $this->addTotals();
    }

    /**
     * Adds a lovely header to the pdf
     */
    public function addHeader()
    {
        $this->_pdf->resetCursor();
        $yStart = $this->_pdf->GetY(); // get current Y
        // add logo
        $logoSrc = public_path($this->_pdf->getSetting('pdf_logo'));
        if (file_exists($logoSrc)) {
            $this->_pdf->Image($logoSrc);
        }
        $imageBottomY = $this->_pdf->GetY();
        // add header text
        $this->_pdf->SetXEnd(-100, $yStart); // but start X 100mm from the end & reset Y to same pos as logo
        $this->_pdf->MultiLineCell(100, null, $this->_pdf->getSetting('pdf_header'), 0, 'R');
        // set Y to below logo and text
        $this->_pdf->resetCursor(max($this->_pdf->GetY(), $imageBottomY));
    }

    /**
     * Adds order number & date
     */
    public function addNumberAndDate()
    {
        $this->_pdf->resetCursor();
        $y = $this->_pdf->GetY(); // get current Y
        $this->_pdf->SetHeaderFont();
        $this->_pdf->MultiCell(100,null, 'Order:  ' . $this->_order->order_number);
        $this->_pdf->SetXEnd(-100, $y);
        $this->_pdf->MultiCell(100,null, 'Date:  ' . ($this->_order->order_placed ? $this->_order->order_placed->format('dS M Y') : 'N/A'), 0, 'R');
        $this->_pdf->resetCursor();
    }

    /**
     * Adds address details (shipping & billing)
     */
    public function addAddresses()
    {
        $this->_pdf->resetCursor();
        $y = $this->_pdf->GetY(); // get current Y
        $cellWidth = $this->_pdf->GetWritableWidth() / 2;
        $this->_pdf->SetFillColor(210, 210, 210); // for billing/shipping headers
        // billing address
        $this->_pdf->SetHeader2Font();
        $this->_pdf->MultiCell($cellWidth,null, 'Billing Address', 1, 'J', 1, [5,1]);
        $this->_pdf->SetDefaultFont();
        $this->_pdf->MultiLineCell($cellWidth, null, $this->_getAddressText($this->_order->billingAddress()), 'O', 'L', 0, [5,2]);
        // shipping address
        $this->_pdf->SetXStart($cellWidth, $y); // pos after billing box
        $this->_pdf->SetHeader2Font();
        $this->_pdf->MultiCell($cellWidth,null, 'Shipping Address', 1, 'J', 1, [5,1]);
        $this->_pdf->SetXStart($cellWidth); // update pos, X reset after Cell
        $this->_pdf->SetDefaultFont();
        $this->_pdf->MultiLineCell($cellWidth, null,$this->_getAddressText($this->_order->shippingAddress()), 'O', 'L', false, [5,2]);
        $this->_pdf->resetCursor();
    }

    /**
     * @param Address $address
     * @return string
     */
    protected function _getAddressText(Address $address = null)
    {
        if (!$address) {
            return '';
        }
        $lines[] = $address->fullName();
        $lines[] = $address->company;
        $lines[] = $address->address_line_1;
        $lines[] = $address->address_line_2;
        $lines[] = implode(", ", array_filter([$address->town, $address->county, $address->postcode]));
        $lines[] = $address->country();
        $lines[] = $address->phone ? 'T: ' . $address->phone : '';
        $lines[] = 'E: ' . ($address->email ?: $this->_order->email);
        return implode("\n",  array_filter($lines));
    }

    /**
     * Adds payment / shipping methods
     */
    public function addPaymentShippingMethods()
    {
        $this->_pdf->resetCursor();
        $y = $this->_pdf->GetY(); // get current Y
        $cellWidth = $this->_pdf->GetWritableWidth() / 2;
        $this->_pdf->SetFillColor(210, 210, 210); // for billing/shipping headers
        // billing address
        $this->_pdf->SetHeader2Font();
        $this->_pdf->MultiCell($cellWidth,null, 'Payment Method', 1, 'L', 1, [5,1]);
        $this->_pdf->SetDefaultFont();
        $paymentMethod = $this->_order->getPaymentMethod();
        $this->_pdf->MultiLineCell($cellWidth, null, $paymentMethod ? $paymentMethod->name : $this->_order->payment_method, 'O', 'L', 0, [5,2]);
        // shipping address
        $this->_pdf->SetXStart($cellWidth, $y); // pos after billing box
        $this->_pdf->SetHeader2Font();
        $this->_pdf->MultiCell($cellWidth,null, 'Shipping Method', 1, 'L', 1, [5,1]);
        $this->_pdf->SetXStart($cellWidth); // update pos, X reset after Cell
        $this->_pdf->SetDefaultFont();
        $shippingMethod = $this->_order->getShippingMethod();
        $this->_pdf->MultiLineCell($cellWidth, null, $shippingMethod ? $shippingMethod->name : $this->_order->shipping_method, 'O', 'L', false, [5,2]);
        $this->_pdf->resetCursor();
    }

    /**
     * Adds Order Items
     */
    public function addOrderItems()
    {
        $this->_pdf->resetCursor();
        $cataloguePrices = Setting::getValue('vat_catalogue_display');
        $cataloguePrices = $this->_order->totalVAT() ? $cataloguePrices : 'inc'; // setting to inc should hide any vat
        $subTotalVAT = $this->_order->order_vat_type  == 'order' ? 'ex' : 'inc';
        $showUnitVAT = $this->_order->totalVAT() && $this->_order->order_vat_type == 'unit';
        $showLineVAT = $this->_order->totalVAT() && $this->_order->order_vat_type  == 'item';
        // calc col widths
        $prodWidth = $this->_pdf->GetWritableWidth() / 3;
        $skuWidth = $this->_pdf->GetWritableWidth() / 6;
        $qtyWidth = $this->_pdf->GetWritableWidth() / 12;
        $priceWidth = ($this->_pdf->GetWritableWidth() - ($prodWidth + $skuWidth + $qtyWidth)) / ($showUnitVAT || $showLineVAT ? 3 : 2);
        $unitVatWidth = $showUnitVAT ? $priceWidth : 0;
        $lineVatWidth = $showLineVAT ? $priceWidth : 0;
        $subtotalWidth = $priceWidth;
        // table headers
        $this->_addOrderItemsHeader($prodWidth, $skuWidth, $priceWidth, $unitVatWidth, $qtyWidth, $lineVatWidth, $subtotalWidth);
        // table contents
        $y = $this->_pdf->GetY();
        foreach ($this->_order->items as $item) {
            $optionTxt = '';
            if ($itemOptions = $item->getDataArray()) {
                $options = [];
                foreach ($item->getDataArray() as $option => $value) {
                    $options[] = $option . ": " . $value;
                }
                $optionTxt = "\n" . implode(", ", $options);
            }
            // checks for new page, if so reset $y and print header again
            $tmp = $this->_pdf->disableWrite();
            $yBefore = $this->_pdf->GetY();
            $pageNoBefore = $this->_pdf->PageNo();
            $this->_pdf->MultiCell($prodWidth,null, $item->item_name . $optionTxt, 0, 'L');
            $pageNoAfter = $this->_pdf->PageNo();
            $this->_pdf->enableWrite($tmp);
            if ($pageNoBefore != $pageNoAfter) {
                $this->_pdf->SetYStart(0);
                $this->_addOrderItemsHeader($prodWidth, $skuWidth, $priceWidth, $unitVatWidth, $qtyWidth, $lineVatWidth, $subtotalWidth);
                $y = $this->_pdf->GetY();
            } else {
                $this->_pdf->SetY($yBefore); // pretend like nothing happened!
            }
            // print row details for item
            $this->_pdf->MultiCell($prodWidth,null, $item->item_name . $optionTxt, 0, 'L');
            $nextY = $this->_pdf->GetY();
            $this->_pdf->SetXStart($prodWidth, $y);
            $this->_pdf->MultiCell($skuWidth,null, $item->item_sku, 0, 'L');
            $nextY = max($nextY, $this->_pdf->GetY());
            $this->_pdf->SetXStart($prodWidth + $skuWidth, $y);
            $this->_pdf->MultiCell($priceWidth,null, new Format($item->getCost('price', $cataloguePrices)), 0, 'R');
            if ($unitVatWidth) {
                $this->_pdf->SetXStart($prodWidth + $skuWidth + $priceWidth, $y);
                $this->_pdf->MultiCell($unitVatWidth,null, (new Format($item->item_unit_vat))->showZero(), 0, 'R');
            }
            $this->_pdf->SetXStart($prodWidth + $skuWidth + $priceWidth + $unitVatWidth, $y);
            $this->_pdf->MultiCell($qtyWidth,null, $item->item_qty, 0, 'R');
            if ($lineVatWidth) {
                $this->_pdf->SetXStart($prodWidth + $skuWidth + $priceWidth + $unitVatWidth + $qtyWidth, $y);
                $this->_pdf->MultiCell($lineVatWidth,null, (new Format($item->item_total_vat))->showZero(), 0, 'R');
            }
            $this->_pdf->SetXStart($prodWidth + $skuWidth + $priceWidth + $unitVatWidth + $qtyWidth + $lineVatWidth, $y);
            $this->_pdf->MultiCell($subtotalWidth,null, new Format($item->getCost('total', $subTotalVAT)), 0, 'R');
            $y = $nextY + 4;
            $this->_pdf->SetXStart(0, $y);
        }
        $this->_pdf->resetCursor();
    }

    /**
     * @param float $prodWidth
     * @param float $skuWidth
     * @param float $priceWidth
     * @param float $unitVatWidth
     * @param float $qtyWidth
     * @param float $lineVatWidth
     * @param float $subtotalWidth
     */
    protected function _addOrderItemsHeader($prodWidth, $skuWidth, $priceWidth, $unitVatWidth, $qtyWidth, $lineVatWidth, $subtotalWidth)
    {
        $this->_pdf->SetDefaultFont();
        $this->_pdf->SetFillColor(210, 210, 210); // for col headers
        $y = $this->_pdf->GetY();
        $this->_pdf->SetXStart(0, $y);
        $this->_pdf->MultiCell($prodWidth,null, 'Product', 1, 'L', 1);
        $this->_pdf->SetXStart($prodWidth, $y);
        $this->_pdf->MultiCell($skuWidth,null, 'SKU', 1, 'L', 1);
        $this->_pdf->SetXStart($prodWidth + $skuWidth, $y);
        $this->_pdf->MultiCell($priceWidth,null, 'Price', 1, 'L', 1);
        if ($unitVatWidth) {
            $this->_pdf->SetXStart($prodWidth + $skuWidth + $priceWidth, $y);
            $this->_pdf->MultiCell($unitVatWidth,null, 'VAT', 1, 'L', 1);
        }
        $this->_pdf->SetXStart($prodWidth + $skuWidth + $priceWidth + $unitVatWidth, $y);
        $this->_pdf->MultiCell($qtyWidth,null, 'Qty', 1, 'L', 1);
        if ($lineVatWidth) {
            $this->_pdf->SetXStart($prodWidth + $skuWidth + $priceWidth + $unitVatWidth + $qtyWidth, $y);
            $this->_pdf->MultiCell($lineVatWidth,null, 'VAT', 1, 'L', 1);
        }
        $this->_pdf->SetXStart($prodWidth + $skuWidth + $priceWidth + $unitVatWidth + $qtyWidth + $lineVatWidth, $y);
        $this->_pdf->MultiCell($subtotalWidth,null, 'Subtotal', 1, 'L', 1);$y = $this->_pdf->GetY();
        $this->_pdf->SetXStart(0, $y + 4);
    }

    /**
     * Adds totals
     */
    public function addTotals()
    {
        $this->_pdf->resetCursor();
        $totalsVAT = $this->_order->order_vat_type  == 'order' ? 'ex' : 'inc';
        $totalsSuffix = '';
        if ($this->_order->totalVAT()) {
            $totalsSuffix = $totalsVAT == 'inc' ? ' (Inc. VAT)' : ' (Ex. VAT)';
        }
        if ($this->_order->order_coupon) {
            $this->_pdf->SetXEnd(-100);
            $this->_pdf->Cell(100,null, 'Coupon: ' . ucwords($this->_order->order_coupon), 1, 1,'R');
        }
        // subtotal & subtotal discount
        $this->_pdf->SetXEnd(-80);
        $this->_pdf->Cell(50,null, 'Subtotal ' . $totalsSuffix , 0, 0,'R');
        $this->_pdf->SetXEnd(-30);
        $this->_pdf->Cell(30,null, new Format($this->_order->getCost('subtotal', $totalsVAT)), 0, 1,'R');
        if ($this->_order->order_subtotal_discount_inc_vat > 0) {
            $this->_pdf->SetXEnd(-80);
            $this->_pdf->Cell(50,null, 'Subtotal Discount' , 0, 0,'R');
            $this->_pdf->SetXEnd(-30);
            $this->_pdf->Cell(30,null, '-' . (new Format($this->_order->getCost('subtotal_discount', $totalsVAT))), 0, 1,'R');
        }
        // shipping & shipping discount
        if ($this->_order->shipping_method) {
            $this->_pdf->SetXEnd(-80);
            $this->_pdf->Cell(50,null, 'Shipping ' . $totalsSuffix , 0, 0,'R');
            $this->_pdf->SetXEnd(-30);
            $this->_pdf->Cell(30,null, new Format($this->_order->getCost('shipping', $totalsVAT)), 0, 1,'R');
            if ($this->_order->order_shipping_discount_inc_vat > 0) {
                $this->_pdf->SetXEnd(-80);
                $this->_pdf->Cell(50,null, 'Shipping Discount', 0, 0,'R');
                $this->_pdf->SetXEnd(-30);
                $this->_pdf->Cell(30,null, '-' . (new Format($this->_order->getCost('shipping_discount', $totalsVAT))), 0, 1,'R');
            }
        }
        // vat
        if ($vat = $this->_order->totalVAT()) {
            $this->_pdf->SetXEnd(-80);
            $this->_pdf->Cell(50,null, 'VAT', 0, 0,'R');
            $this->_pdf->SetXEnd(-30);
            $this->_pdf->Cell(30,null, (new Format($vat))->showZero(), 0, 1,'R');
        }
        // order total
        $this->_pdf->SetBoldFont();
        $this->_pdf->SetXEnd(-80);
        $this->_pdf->Cell(50,null, 'Order Total', 0, 0,'R');
        $this->_pdf->SetXEnd(-30);
        $this->_pdf->Cell(30,null, new Format($this->_order->order_total_inc_vat), 0, 1,'R');
        $this->_pdf->resetCursor();
    }

    /**
     * @return BasePDF
     */
    public function getPdf()
    {
        return $this->_pdf;
    }

}