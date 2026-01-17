<?php

namespace App\Repositories\Dashboard;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Supplier;
use App\Models\User;
use App\Repositories\BaseRepository;
use Carbon\Carbon;

class DashBoardRepository extends BaseRepository implements DashboardRepositoryInterface
{
    public function getModel(): string
    {
        return User::class;
    }

    public function getDashboard($userId)
    {
        $user = $this->find($userId);
        $data = [];
        $now = Carbon::now();

        $sevenDaysAgo = Carbon::now()->subDays(6)->startOfDay();
        $thirtyDaysAgo = Carbon::now()->subDays(29)->startOfDay();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Orders this week
        $order_week = Order::query()
            ->where('user_id', $userId)
            ->where('type', 1)
            ->whereBetween('created_at', [$sevenDaysAgo, $now]);
        $total_order_week = $order_week->count();
        $order_success_week = (clone $order_week)->where('status', Order::SUCCESS)->get();
        $retail_cost = $order_success_week->sum('retail_cost');
        $base_cost = $order_success_week->sum('base_cost');
        $revenue = $retail_cost - $base_cost;
        $average_order_value = $order_success_week->count() > 0 ? $retail_cost / $order_success_week->count() : 0;
        $order_conversion_rate = $total_order_week > 0 ? ($order_success_week->count() / $total_order_week) * 100 : 0;

        // Orders this month
        $order_month = Order::query()
            ->where('user_id', $userId)
            ->where('type', 1)
            ->whereBetween('created_at', [$startOfMonth, $now]);
        $total_order_month = $order_month->count();
        $order_success_month = (clone $order_month)->where('status', Order::SUCCESS)->get();
        $revenue_month = $order_success_month->sum('retail_cost') - $order_success_month->sum('base_cost');

        // Top 5 selling products this week (if product_id and quantity fields exist)
        $top_products = OrderDetail::query()
            ->selectRaw('product_id, SUM(quantity) as total_quantity')
            ->whereHas('product', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereBetween('created_at', [$sevenDaysAgo, $now])
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        // Pending orders count
        $pending_orders = Order::query()
            ->where('user_id', $userId)
            ->where('type', 1)
            ->where('status', Order::PENDING)
            ->count();

        // Recent 5 orders
        $recent_orders = Order::query()
            ->where('user_id', $userId)
            ->where('type', 1)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // New customers this week
        $new_customers_week = Customer::query()
            ->where('user_id', $userId)
            ->where('status', 1)
            ->whereBetween('created_at', [$sevenDaysAgo, $now])
            ->count();

        // Total customers and suppliers
        $total_customer = Customer::query()
            ->where('user_id', $userId)
            ->where('status', 1)
            ->count();
        $total_supplier = Supplier::query()
            ->where('user_id', $userId)
            ->where('status', 1)
            ->count();

        // Outstanding payments - using retail_cost instead of non-existent 'total' and 'paid' columns
        // Check if payment_status is needed for distinguishing paid vs. unpaid orders
        $outstanding_payments = Order::query()
            ->where('user_id', $userId)
            ->where('type', 1)
            ->where('status', Order::SUCCESS)
            ->where('payment_status', '!=', 1) // Assuming payment_status=1 means fully paid
            ->sum('retail_cost');

        $data = [
            'total_order_week' => $total_order_week,
            'revenue' => $revenue,
            'average_order_value' => $average_order_value,
            'order_conversion_rate' => $order_conversion_rate,
            'total_order_month' => $total_order_month,
            'revenue_month' => $revenue_month,
            'top_products_week' => $top_products,
            'pending_orders' => $pending_orders,
            'recent_orders' => $recent_orders,
            'new_customers_week' => $new_customers_week,
            'total_customer' => $total_customer,
            'total_supplier' => $total_supplier,
            'outstanding_payments' => $outstanding_payments,
        ];
        return $data;
    }
}
