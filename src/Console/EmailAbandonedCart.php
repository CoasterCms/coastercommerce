<?php

namespace CoasterCommerce\Core\Console;

use Carbon\Carbon;
use CoasterCommerce\Core\Mailables\AbandonedCartMailable;
use CoasterCommerce\Core\Model\AbandonedCart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EmailAbandonedCart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:abandoned-cart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send abandoned cart emails';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $abandonedCarts = AbandonedCart::updateAndReturnAbandonedCarts();
        $unsubscribed = AbandonedCart\Unsubscribed::all()->pluck('email')->toArray();

        $emails = 0;
        foreach ($abandonedCarts as $abandonedCart) {
            if ($abandonedCart->order_converted) {
                continue;
            }
            if (in_array($abandonedCart->email, $unsubscribed)) {
                continue;
            }
            /** @var Carbon $lastUpdated */
            $lastUpdated = $abandonedCart->order->updated_at;
            $hoursOld = $lastUpdated->diffInHours();
            if ($abandonedCart->emails_sent == 0 && $hoursOld >= 1) {
                $send = true;
            } elseif ($abandonedCart->emails_sent == 1 && $hoursOld >= 24) {
                $send = false;
            } elseif ($abandonedCart->emails_sent == 2 && $hoursOld >= 168) {
                $send = false;
            } else {
                $send = false;
            }
            if ($send) {
                $emails++;
                $abandonedCart->emails_sent++;
                $abandonedCart->email_last_sent = Carbon::now();
                $abandonedCart->save();
                Mail::send(new AbandonedCartMailable($abandonedCart));
            }
        }

        $this->line('Sent (' . $emails . ') abandoned cart emails to customers.');
    }
}
