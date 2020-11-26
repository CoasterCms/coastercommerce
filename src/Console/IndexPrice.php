<?php

namespace CoasterCommerce\Core\Console;

use Carbon\Carbon;
use CoasterCommerce\Core\Model\Product;
use CoasterCommerce\Core\Model\Product\SearchIndex\Price;
use Illuminate\Console\Command;

class IndexPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:price {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update search price index';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $productsQuery = Product::with(['variations', 'categories']);
        if (!$this->option('all')) {
            $reindexProductIds = Price::whereDate('expires', '<', Carbon::now())->groupBy('product_id')->pluck('product_id')->toArray();
            $productsQuery->whereIn('id', $reindexProductIds);
        }
        $products = $productsQuery->get(['id', 'price']);
        (new Price())->reindexAll($products, false);
        $this->line('Re-indexed (' . $products->count() . ') product prices.');
    }

}
