<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\ReceiptPayment;
use Illuminate\Console\Command;
use App\Models\ProfitLoss as ProfitLostModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfitLoss extends Command
{
    protected $signature = 'app:profit-loss-daily';
    protected $description = 'Insert daily profit and loss data into the database';

    public function handle()
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $timeRange = [
            "$yesterday 00:00:00",
            "$yesterday 23:59:59"
        ];

        Log::info("Processing date: $yesterday");

        try {
            $this->processRevenueSales($timeRange, $yesterday);
            $this->processOtherTransactions($timeRange, $yesterday);
        } catch (\Exception $exception) {
            Log::error($exception);
        }
    }

    private function processRevenueSales(array $timeRange, string $date)
    {
        $revenueSales = Order::query()
            ->select([
                'id',
                'retail_cost',
                'base_cost',
                'retail_cost_base',
                'entry_cost',
                'vat',
                'status',
                'user_id',
            ])
            ->where(function ($query) {
                $query->whereIn('status', [Order::SUCCESS, Order::RETURN , Order::CANCEL_SUCCESS]);
            })
            ->where(function ($query) {
                $query->whereIn('type', [1, 3]);
            })
            ->whereBetween('updated_at', $timeRange)
            ->get()
            ->groupBy('user_id');

        foreach ($revenueSales as $userId => $items) {
            $data = [
                'user_id' => $userId,
                'revenue_sale' => 0,
                'discount_sale' => 0,
                'cost_sale' => 0,
                'vat' => 0,
                'fee' => 0,
                'order_cancel' => 0,
                'time' => $date,
            ];

            foreach ($items as $item) {
                $vatRetail = $item->retail_cost - ($item->retail_cost * 100 / (100 + $item->vat));

                if ($item->status == Order::SUCCESS) {
                    $data['revenue_sale'] += $item->retail_cost_base;
                    $data['cost_sale'] += $item->entry_cost;
                    $data['vat'] += $vatRetail;
                    $data['discount_sale'] += $item->retail_cost_base - $item->retail_cost + $vatRetail;
                } else if ($item->status == Order::RETURN) {
                    $data['vat'] += $vatRetail;
                    $data['order_cancel'] += $item->retail_cost - $vatRetail;
                    $data['cost_sale'] -= $item->entry_cost;
                }

                // Disable timestamps to prevent updated_at change
                $item->timestamps = false;
                $item->save();
            }

            $data['discount_sale'] = max(0, $data['discount_sale']);
            ProfitLostModel::create($data);
        }
    }

    private function processOtherTransactions(array $timeRange, string $date)
    {
        // Process other incomes
        $this->processTransactionType(1, 'other_income', $timeRange, $date);

        // Process other costs
        $this->processTransactionType(2, 'other_cost', $timeRange, $date);
    }

    private function processTransactionType(int $type, string $fieldName, array $timeRange, string $date)
    {
        $transactions = ReceiptPayment::query()
            ->select('user_id', DB::raw('sum(price) as price_total'))
            ->where('type', $type)
            ->where('is_other_income', true)
            ->where('status', 1)
            ->where('is_edit', true)
            ->whereBetween('time', $timeRange)
            ->groupBy('user_id')
            ->get();

        foreach ($transactions as $transaction) {
            $profitLost = ProfitLostModel::query()
                ->where('user_id', $transaction->user_id)
                ->where('time', $date)
                ->first();

            if ($profitLost) {
                $profitLost->$fieldName = $transaction->price_total ?: 0;
                $profitLost->save();
            } else {
                ProfitLostModel::create([
                    'user_id' => $transaction->user_id,
                    $fieldName => $transaction->price_total ?: 0,
                    'fee' => 0,
                    'order_cancel' => 0,
                    'time' => $date,
                ]);
            }
        }
    }
}
