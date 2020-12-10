<?php namespace CoasterCommerce\Core\Listeners;

use CoasterCommerce\Core\Events\AdminEmailSave as AdminEmailSaveEvent;
use CoasterCommerce\Core\Mailables\AbstractMailable;

class AdminEmailSave
{

    /**
     * @param AdminEmailSaveEvent $event
     */
    public function handle(AdminEmailSaveEvent $event)
    {
        /** @var AbstractMailable $mailableClass */
        $mailableClass = $event->email->mailable;
        $mailable = $mailableClass::testInstance();

        $viewPath = view($mailable->getMarkdownView())->getPath();
        $currentTemplate = trim(trim(str_replace("\r\n", "\n", file_get_contents($viewPath))));
        $newTemplate = trim(trim(str_replace("\r\n", "\n", $event->inputData['contents'])));
        if (stripos($viewPath, 'vendor/coastercms/coastercommerce/resources/views') !== false) {
            // vendor/coastercms/coastercommerce/resources/views => resources/views/vendor/coaster-commerce
            $viewPath = str_replace('vendor/coastercms/coastercommerce/resources/views', 'resources/views/vendor/coaster-commerce', $viewPath);
        }

        if ($currentTemplate !== $newTemplate) {

            $viewDir = dirname($viewPath);
            if (!file_exists($viewDir)) {
                mkdir($viewDir, 0777, true);
            }

            file_put_contents($viewPath, $newTemplate);
        }
    }

}

