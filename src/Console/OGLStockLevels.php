<?php

namespace CoasterCommerce\Core\Console;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class OGLStockLevels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ogl:stock-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update product quantity';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stock_levels = DB::table('STStockDetails')->select('lev', 'stcode', 'tradeprice_1', 'listprice_1')->get()->keyBy('stcode');
        $prod_ids = DB::table('cc_products')->pluck('id', 'sku');
       
        foreach ($stock_levels as $st_code => $stock_level) {
            if ($prod_ids->has($st_code)) {
                DB::table('cc_product_eav_integer')
                    ->where('product_id', $prod_ids[$st_code])
                    ->where('attribute_id', 16)
                    ->update(['value' => $stock_level->lev]);

                DB::table('cc_product_advanced_pricing')->updateOrInsert(
                    [
                        'product_id' => $prod_ids[$st_code],
                        'group_id' => 4,
                        'min_quantity' => 0
                    ],
                    [
                        'price' => $stock_level->tradeprice_1
                    ]
                );

                DB::table('cc_product_advanced_pricing')->updateOrInsert(
                    [
                        'product_id' => $prod_ids[$st_code],
                        'group_id' => 3,
                        'min_quantity' => 0
                    ],
                    [
                        'price' => $stock_level->tradeprice_1
                    ]
                );

                DB::table('cc_products')->where('id', $prod_ids[$st_code])
                                        ->update(
                                            [
                                                'price' => $stock_level->listprice_1
                                            ]
                                        );
            }
        }
    }
}
