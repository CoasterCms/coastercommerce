<?php

namespace CoasterCommerce\Core\Mailables;

use CoasterCommerce\Core\Model\EmailSetting;
use CoasterCommerce\Core\Model\Setting;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Mail\Mailable;

abstract class AbstractMailable extends Mailable
{

    /**
     * @var bool
     */
    protected $_isTest = false;

    /**
     * @param  \Illuminate\Contracts\Mail\Factory|\Illuminate\Contracts\Mail\Mailer  $mailer
     */
    public function send($mailer)
    {
        $from = [Setting::getValue('email_sender_address'), Setting::getValue('email_sender_name')];
        if ($settings = $this->getSettings()) {

            if (!$settings->enabled && !$this->_isTest) {
                return null;
            }

            foreach (['to', 'cc', 'bcc'] as $type) {
                $additionalRecipients = array_filter(array_map('trim', explode(',', $settings->$type)));
                foreach ($additionalRecipients as $additionalRecipient) {
                    $this->$type[] = ['address' => $additionalRecipient, 'name' => null];
                }
            }

            // send to store contact if no to address
            $this->callbacks[] = function (\Swift_Message $message) {
                if (!$message->getTo()) {
                    $message->setTo(Setting::getValue('store_email'));
                }
            };

            $from[0] = $settings->from_email ?: $from[0];
            $from[1] = $settings->from_name ?: $from[1];
            $this->subject($settings->subject);
        }
        $this->from(...$from);
        parent::send($mailer);
    }

    /**
     * @return EmailSetting
     */
    public function getSettings()
    {
        return EmailSetting::getSettings(get_class($this));
    }

    /**
     * @return string
     */
    public function getMarkdownView()
    {
        return $this->build()->markdown;
    }

    /**
     * Allows mail to send even if disabled
     * @return $this
     */
    public function isTest()
    {
        $this->_isTest = true;
        return $this;
    }

    /**
     * @return array
     */
    public static function testData()
    {
        return [];
    }

    /**
     * @return static
     */
    public static function testInstance()
    {
        return new static(...static::testData());
    }

    /**
     * @param string $recipient
     */
    public static function sendTest($recipient)
    {
        $mailable = static::testInstance();
        $mailable->callbacks[] = function (\Swift_Message $message) use($recipient) {
            $message->setTo($recipient)->setCc([])->setBcc([]);
        };
        $mailable->isTest()->send(app('mailer'));
    }

}

