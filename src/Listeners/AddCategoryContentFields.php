<?php namespace CoasterCommerce\Core\Listeners;

use CoasterCommerce\Core\Events\CategoryRenderContentFields;
use CoasterCommerce\Core\Renderer\Admin\Attribute;

class AddCategoryContentFields
{

    /**
     * @param CategoryRenderContentFields $event
     */
    public function handle(CategoryRenderContentFields $event)
    {
        // add listener in app folder to add additional fields (for example intro)
        $event->html .=
            // (new Attribute('intro', 'wysiwyg', 'Intro / Short Description'))->renderInput($event->category->intro) .
            (new Attribute('content', 'wysiwyg', 'Main Content'))->renderInput($event->category->content);
    }

}

