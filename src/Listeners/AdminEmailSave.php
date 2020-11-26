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
        if (stripos($viewPath, 'vendor/coastercommerce/core') !== false && stripos($viewPath, 'vendor/coaster-commerce') === false) {
            // if using view in vendor folder overwrite with views/vendor
            $viewPath = resource_path('views/vendor/coaster-commerce' . substr($viewPath, stripos($viewPath, 'resources/views') + 15));
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

