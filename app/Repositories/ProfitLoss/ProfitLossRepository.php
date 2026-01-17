<?php

namespace App\Repositories\ProfitLoss;

use App\Models\Order;
use App\Models\ProfitLoss;
use App\Models\ReceiptPayment;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProfitLossRepository extends BaseRepository implements ProfitLossRepositoryInterface
{


    /**
     * @inheritDoc
     */
    public function getModel()
    {
        return ProfitLoss::class;
    }

    /**
     * @inheritDoc
     */
    public function getReport($request)
    {
        $query = $this->model->select(
            DB::raw('sum(revenue_sale) as revenue_sale'),
            DB::raw('sum(discount_sale) as discount_sale'),
            DB::raw('sum(order_cancel) as order_cancel'),
            DB::raw('sum(cost_sale) as cost_sale'),
            DB::raw('sum(vat) as vat'),
            DB::raw('sum(other_income) as other_income'),
            DB::raw('sum(other_expense) as other_expense'),

        );

        if ($request->start_date && $request->end_date) {
            $query->where('time', '>=', $request->start_date)
                ->where('time', '<=', $request->end_date);
        } else {
            $query->whereBetween('time', [Carbon::now()->startOfMonth()->format('Y-m-d'), Carbon::yesterday()->format('Y-m-d')]);
        }

        build_query_by_user_id($query, auth()->user());

        return $query->first()->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getReportToday()
    {
        $today = Carbon::today();
        $start = $today->copy()->startOfDay()->toDateTimeString();
        $end = $today->copy()->endOfDay()->toDateTimeString();
        $date = $today->toDateString();
        $userId = auth()->id();
        $user = auth()->user();

        // Initialize data structure
        $data = [
            'user_id' => $userId,
            'revenue_sale' => 0,
            'cost_sale' => 0,
            'discount_sale' => 0,
            'vat' => 0,
            'order_cancel' => 0,
            'other_income' => 0,
            'other_expense' => 0,
            'time' => $date,
        ];

        // Get revenue data
        $revenueSaleQuery = Order::query()
            ->select(['id', 'retail_cost', 'base_cost', 'retail_cost_base', 'entry_cost', 'vat', 'status'])
            ->whereIn('status', [Order::SUCCESS, Order::RETURN])
            ->where('type', 1)
            ->whereBetween('updated_at', [$start, $end]);

        build_query_by_user_id($revenueSaleQuery, $user);

        // Process order data
        foreach ($revenueSaleQuery->get() as $order) {
            $vatRetail = $order->retail_cost - ($order->retail_cost * 100 / (100 + $order->vat));

            if ($order->status_order == Order::SUCCESS) {
                $data['revenue_sale'] += $order->retail_cost_base;
                $data['cost_sale'] += $order->entry_cost;
                $data['vat'] += $vatRetail;
                $data['discount_sale'] += $order->retail_cost_base - $order->retail_cost + $vatRetail;
            } else if ($order->status_order == Order::RETURN) {
                $data['vat'] += $vatRetail;
                $data['order_cancel'] += $order->retail_cost - $vatRetail;
                $data['cost_sale'] -= $order->entry_cost;
            }
        }

        $data['discount_sale'] = max(0, $data['discount_sale']);

        // Get other income and costs
        $conditions = [
            'is_edit' => true,
            'is_other_income' => true,
            ['time', '>=', $start],
            ['time', '<=', $end]
        ];

        // Other income
        $otherIncomeQuery = ReceiptPayment::where('type', 1)->where($conditions);
        build_query_by_user_id($otherIncomeQuery, $user);
        $data['other_income'] = $otherIncomeQuery->sum('price');

        // Other costs
        $otherCostQuery = ReceiptPayment::where('type', 2)->where($conditions);
        build_query_by_user_id($otherCostQuery, $user);
        $data['other_expense'] = $otherCostQuery->sum('price');

        return $data;
    }

}
