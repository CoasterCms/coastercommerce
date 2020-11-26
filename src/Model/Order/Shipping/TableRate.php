<?php

namespace CoasterCommerce\Core\Model\Order\Shipping;

use CoasterCommerce\Core\Model\Order\Address;
use Illuminate\Http\UploadedFile;
use League\Csv\Reader;

/**
 * Class TableRate
 */
class TableRate extends AbstractShipping
{

    /**
     * @return float
     */
    public function rate()
    {
        $shippingAddress = $this->_order->shippingAddress() ?: new Address();
        $condition = $this->getCustomField('condition');
        if ($this->getCustomField('condition') == 'subtotal') {
            $value = $this->_order->order_subtotal_inc_vat;
        } else {
            $condition = 'weight';
            $weight = (float) 0;
            foreach ($this->_order->items as $item) {
                if ($item->product_id) {
                    $itemWeight = $item->product->weight;
                    if ($item->variation_id) {
                        $itemWeight = is_null($item->variation->weight) ? $itemWeight : $item->variation->weight;
                    }
                    $weight += $itemWeight * $item->item_qty;
                }
            }
            $value = $weight;
        }
        return TableRate\Model::getRateForDestination($this->code, $condition, $value, $shippingAddress->country_iso3, $shippingAddress->postcode);
    }

    /**
     * Renders custom settings in the admin
     * @return string
     */
    public function renderCustomFields()
    {
        return view('coaster-commerce::admin.shipping.method-table', ['method' => $this]);
    }

    /**
     * @param array $config
     */
    public function fillCustomFields($config)
    {
        if ($file = request()->file($this->code . '.new_rates')) {
            $this->saveNewRates($file);
        }
        parent::fillCustomFields($config);
    }

    /**
     * @param UploadedFile $file
     */
    public function saveNewRates(UploadedFile $file)
    {
        $csv = Reader::createFromString($file->get());
        $csv->setHeaderOffset(0);
        $headers = TableRate\Model::getRateHeaders();
        if ($missingHeaders = array_diff($headers, $csv->getHeader())) {
            $this->_alerts['danger'] = 'CSV not uploaded, missing columns : ' . implode(', ', $missingHeaders);
        } else {
            $headerKeys = array_flip($headers);
            $tableRates = collect();
            foreach ($csv->getRecords() as $record) {
                $tableRate = ['method' => $this->code];
                foreach ($record as $header => $value) {
                    if (array_key_exists($header, $headerKeys)) {
                        $value = trim($value);
                        $tableRate[$headerKeys[$header]] = $value === '' ? null : $value;
                    }
                }
                $tableRates[] = $tableRate;
            }
            TableRate\Model::where('method', $this->code)->delete();
            $tableRates->chunk(50)->each(function ($chunk) {
                TableRate\Model::insert($chunk->toArray());
            });
        }
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return !is_null($this->rate());
    }

}
