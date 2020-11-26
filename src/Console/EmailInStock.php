<?php

namespace CoasterCommerce\Core\Console;

use CoasterCommerce\Core\Mailables\InStockMailable;
use CoasterCommerce\Core\Model\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EmailInStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:instock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send in stock email to customers';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $product_ids_in_stock = (new Product)->newModelQuery()
            ->where('stock_qty', '>', 0)
            ->orWhere('stock_managed', false)
            ->pluck('id')->toArray();

        $notifications = DB::table('cc_customer_stock_notify')
            ->select('email', 'product_id')
            ->where('sent', false)
            ->whereIn('product_id', $product_ids_in_stock)->get();

        $products = [];

        foreach ($notifications as $notification) {
            $products[$notification->email][] = $notification->product_id;
        }

        foreach ($products as $email => $product_ids) {
            DB::table('cc_products')->where('id', $product_ids);

            Mail::send(new InStockMailable($email, $product_ids));

            DB::table('cc_customer_stock_notify')->where('email', $email)
                ->whereIn('product_id', $product_ids)
                ->update(['sent' => true]);
        }


        $this->line('Sent (' . count($products) . ') customers back in stock notifications.');
    }
}
