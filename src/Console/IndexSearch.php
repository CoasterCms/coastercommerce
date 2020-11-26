<?php

namespace CoasterCommerce\Core\Console;

use CoasterCommerce\Core\Model\Product;
use Illuminate\Console\Command;

class IndexSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:search {id?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update search index';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($id = $this->argument('id')) {
            if ($product = Product::find($id)) {
                (new Product\SearchIndex())->reindexProduct($product);
                $this->line('Re-indexed product: ' . $product->name . ' [#' . $product->id . ']');
            } else {
                $this->line('Invalid product id.');
            }
        } elseif ($this->option('all')) {
            (new Product\SearchIndex())->reindexAll();
            $products = (new Product)->newModelQuery()->count();
            $this->line('Re-indexed (' . $products . ') products.');
        } else {
            $this->line('Specify product id or use --all flag.');
        }
    }

}
