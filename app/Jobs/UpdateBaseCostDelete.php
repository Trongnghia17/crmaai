<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateBaseCostDelete implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $user;
    protected $order;
    /**
     * Create a new job instance.
     */
    public function __construct($order, $user)
    {
        $this->user = $user;
        $this->order = $order;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = $this->order;
        \Log::info("basecost");
        foreach ($order->orderDetail ?? [] as $item) {
            $orderRefund = \App\Models\OrderRefund::query()
                ->where('order_refund_id', $order->id)
                ->where('product_id', $item['product_id'])
                ->sum('quantity');

            $quantity = $item->quantity - $orderRefund;

            $baseCost = $item->base_cost;
            $baseCost = $this->discount($order, $baseCost);
            $product = Product::query()->whereKey($item['product_id'])->first();
            $availableBase = $product->available + $quantity;


            if ($quantity == 0) {
                if ($product->available <= 0) {
                    $entry_cost = ceil($baseCost / $item->quantity);
                } else {
                    $entry_cost = (($product->entry_cost * $availableBase) - $baseCost) / ($product->available);
                }
                $product->entry_cost = round($entry_cost);
                $product->save();
            }
        }
    }
    private function discount($order, $baseCost)
    {
        if ($order->discount_type == 1) {
            return $baseCost * (100 - $order->discount) / 100;
        } else {
            return $baseCost - $order->discount;
        }
    }
}
