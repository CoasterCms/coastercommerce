<?php namespace CoasterCommerce\Core\Listeners;

use CoasterCommerce\Core\Events\AdminAttributeSave as AdminAttributeSaveEvent;
use CoasterCommerce\Core\Model\Product\Attribute\Eav;
use CoasterCommerce\Core\Model\Product\Attribute\GroupItem;
use CoasterCommerce\Core\Model\Product\Attribute\Meta;

class AdminAttributeSave
{

    /**
     * @param AdminAttributeSaveEvent $event
     */
    public function handle(AdminAttributeSaveEvent $event)
    {
        // save datatype
        if (!$event->attribute->isSystem()) {
            $eavModel = $event->attribute->eav ?: new Eav();
            $eavModel->datatype = $event->inputData['datatype'];
            $event->attribute->eav()->save($eavModel);
        }
        // save group data
        $groupData = request()->post('attribute-group', []);
        $groupItem = GroupItem::where('attribute_id', $event->attribute->id)->first() ?: new GroupItem();
        if (empty($groupData['id'])) {
            $groupItem->delete();
        } else {
            $groupItem->attribute_id = $event->attribute->id;
            $groupItem->group_id = $groupData['id'];
            $groupItem->position = $groupData['position'] ?: 0;
            $groupItem->save();
        }
        // save attribute select options
        if (strpos($event->attribute->frontend, 'select') === 0) {
            $metaOptions =  $event->attribute->meta->where('key', 'options')->first() ?: new Meta();
            $postOptionsData = request()->post('options');
            if ($postOptionsData || $metaOptions->exists) {
                if ($postOptionsData) {
                    $metaOptions->key = 'options';
                    foreach ($postOptionsData as $i => $postOptionData) {
                        foreach ($postOptionData as $key => $value) {
                            $postOptionsData[$i][$key] = is_null($value) ? '' : $value;
                        }
                        if ($postOptionsData[$i]['value'] === '') {
                            $postOptionsData[$i]['value'] = $postOptionsData[$i]['name'];
                        }
                    }
                    $metaOptions->value = json_encode(array_values($postOptionsData));
                    $event->attribute->meta()->save($metaOptions);
                } else {
                    $metaOptions->delete();
                }
            }
        }
    }

}

