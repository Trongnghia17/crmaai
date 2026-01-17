<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Product;
use App\Repositories\Order\OrderRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateBaseCost implements ShouldQueue
{
    protected $user;
    protected $orderId;
    protected $order;
    protected $statusOrder;

    use Queueable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct($orderId, $user, $statusOrder)
    {
        $this->order = new OrderRepository();
        $this->user = $user;
        $this->orderId = $orderId;
        $this->statusOrder = $statusOrder;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = $this->order->find($this->orderId);
        $order->load('orderDetail');
        \Log::info("basecost");
        if ($this->statusOrder === Order::SUCCESS) {
            $totalBaseCost = collect($order->orderDetail)->sum('base_cost');
            foreach ($order->orderDetail ?? [] as $item) {
                $product = Product::query()->whereKey($item['product_id'])->first();
                $availableBase = $product->available - $item->quantity;

                $base_cost = $item->base_cost;
                if ($order->discount > 0) {
                    switch ($order->discount_type) {
                        case 1:
                            $base_cost = $item->base_cost * (100 - $order->discount) / 100;
                            break;
                        case 2:
                            $percent = $item->base_cost / $totalBaseCost;
                            $discount = $order->discount * $percent;
                            $base_cost = $item->base_cost - $discount;
                            break;
                    }
                }

                if ($availableBase <= 0 || $product->entry_cost == 0) {
                    $entry_cost = ceil($base_cost / $item->quantity);
                } else {
                    $entry_cost = ceil((($item->entry_cost * $availableBase / $item->quantity) + $base_cost) / $product->available);
                }

                $product->entry_cost = $entry_cost;
                $product->save();
            }
        } else {
            //todo refund
            foreach ($order->orderDetail ?? [] as $item) {
                $baseCost = $item->base_cost * -1;
                $baseCost = $this->discount($order, $baseCost);
                $product = Product::query()->whereKey($item['product_id'])->first();
                $availableBase = $product->available + $item->quantity;
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
            $baseCostOld = $this->order->getTotalBaseCostByOrderId($order->order_id);
            $percentOfOrder = $baseCost / $baseCostOld;

            $discountOfOrder = $order->discount * $percentOfOrder;
            return $baseCost - $discountOfOrder;
        }
    }
}
