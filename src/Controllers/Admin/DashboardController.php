<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use Carbon\Carbon;
use CoasterCommerce\Core\Model\Order;
use Illuminate\Support\Facades\DB;

class DashboardController extends AbstractController
{

    public function dashboard()
    {
        $this->_setTitle('Dashboard');
        return $this->_view('dashboard.dashboard', [
            'orders' => Order::with(['addresses', 'status'])->whereIn('order_status', Order\Status::visibleStatuses())->orderBy('order_placed', 'desc')->limit(5)->get(),
            'order_submitted_count' => DB::table('cc_orders')->whereIn('order_status', Order\Status::submittedStatuses())->count(),
            'order_submitted_total' => DB::table('cc_orders')->whereIn('order_status', Order\Status::submittedStatuses())->sum('order_total_inc_vat'),
            'order_complete_count' => DB::table('cc_orders')->whereIn('order_status', Order\Status::completeStatuses())->count(),
            'order_complete_total' => DB::table('cc_orders')->whereIn('order_status', Order\Status::completeStatuses())->sum('order_total_inc_vat'),
            'order_complete_quarter_count' => DB::table('cc_orders')->whereIn('order_status', Order\Status::completeStatuses())->where('order_placed', '>', (new Carbon())->modify('-3 months'))->count(),
            'order_complete_quarter_total' => DB::table('cc_orders')->whereIn('order_status', Order\Status::completeStatuses())->where('order_placed', '>', (new Carbon())->modify('-3 months'))->sum('order_total_inc_vat'),
            'customer_count' => DB::table('cc_customers')->count(),
            'customer_count_active' => DB::table('cc_customers')->whereDate('last_login', '>', (new Carbon())->modify('-3 months'))->count(),
            'product_count' => DB::table('cc_products')->count(),
            'product_count_enabled' => DB::table('cc_products')->where('enabled', 1)->count()
        ]);
    }

    public function undefinedRoute()
    {
        return $this->_notFoundView();
    }

}
