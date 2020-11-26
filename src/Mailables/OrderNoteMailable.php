<?php

namespace CoasterCommerce\Core\Mailables;

use CoasterCommerce\Core\Model\Order\Note;

class OrderNoteMailable extends AbstractMailable
{

    /**
     * @var Note
     */
    protected $note;

    /**
     * Create a new message instance.
     *
     * @param Note $note
     * @return void
     */
    public function __construct($note)
    {
        $this->note = $note;
    }

    /**
     * @return static
     */
    public function build()
    {
        $toAddress = $this->note->order->email;
        if ($shippingAddress = $this->note->order->shippingAddress()) {
            $toAddress = $shippingAddress->email ?: $toAddress;
        }
        return $this
            ->to($toAddress)
            ->markdown('coaster-commerce::emails.templates.order-note', [
                'note' => $this->note
            ]);
    }

    /**
     * @param string $subject
     * @return AbstractMailable
     */
    public function subject($subject)
    {
        $subject = str_replace('%order_number', $this->note->order->order_number, $subject);
        return parent::subject($subject);
    }

    /**
     * @return array
     */
    public static function testData()
    {
        $note = new Note();
        $note->order = OrderMailable::testData()[0];
        $note->note = 'This is a test comment';
        return [$note];
    }

}
