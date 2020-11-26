<?php namespace CoasterCommerce\Core\Listeners;

use CoasterCommerce\Core\Events\AdminCustomerSave as AdminCustomerSaveEvent;
use CoasterCommerce\Core\Model\Customer\Address;
use CoasterCommerce\Core\Model\Customer\Meta;
use Illuminate\Database\Eloquent\Collection;

class AdminCustomerSave
{

    /**
     * @param AdminCustomerSaveEvent $event
     */
    public function handle(AdminCustomerSaveEvent $event)
    {
        // update customer meta
        $newMeta = [];
        if ($metaPostData = request()->get('meta', [])) {
            if (array_key_exists('key', $metaPostData)) {
                foreach ($metaPostData['key'] as $i => $key) {
                    if (!is_null($key) && $key !== '') {
                        $newMeta[$key] = $metaPostData['value'][$i];
                    }
                }
            }
        }
        $currentMeta = $event->customer->meta->keyBy('key');
        $keepIds = [];
        $newMetaModels = [];
        foreach ($newMeta as $key => $value) {
            $metaModel = $currentMeta->offsetExists($key) ? $currentMeta->offsetGet($key) : new Meta();
            $metaModel->key = $key;
            $metaModel->value = $value;
            $newMetaModels[] = $metaModel;
            if ($metaModel->id) {
                $keepIds[] = $metaModel->id;
            }
        }
        $event->customer->meta()->whereNotIn('id', $keepIds)->delete();
        $event->customer->meta()->saveMany($newMetaModels);
        $event->customer->load('meta');

        // update address
        /** @var Collection $currentAddresses */
        $currentAddresses = $event->customer->addresses->keyBy('id');

        $updatedAddressIds = [];
        $addressPostData = request()->post('address', []);
        foreach ($addressPostData as $id => $addressData) {
            if ($currentAddresses->offsetExists($id)) {
                $address = $currentAddresses->offsetGet($id);
            } else {
                $address = new Address();
                if (count(array_filter($addressData)) < 2) {
                    continue; // country is always filled, must be at least one other field before saving
                }
            }
            $address->forceFill(array_map(function ($value) {
                return is_null($value) ? '' : $value;
            }, $addressData) + ['customer_id' => $event->customer->id])->save();
            $updatedAddressIds[] = $address->id;
        }

        $event->customer->addresses()->whereNotIn('id', $updatedAddressIds)->delete();

    }

}

