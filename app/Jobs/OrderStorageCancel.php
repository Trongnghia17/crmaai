<?php

namespace App\Jobs;

use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\ProductStorage\ProductStorageRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OrderStorageCancel implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Dispatchable, SerializesModels;
    protected $productStorage;
    protected $order;
    protected $type;
    protected $user;
    protected $orderId;
    protected $action;

    public function __construct($orderId, $user, $type, $action = 'create')
    {
        $this->productStorage = app(ProductStorageRepositoryInterface::class);
        $this->order = app(OrderRepositoryInterface::class);
        $this->type = $type;
        $this->user = $user;
        $this->orderId = $orderId;
        $this->action = $action;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("==== Start Product Storage ======");
        $order = $this->order->find($this->orderId);
        if ($order) {
            $order->load('orderDetail.product');
            Log::info($order);
            foreach ($order->orderDetail ?? [] as $item) {
                $this->__deleteProductStorage($item);
            }
        } else {
            Log::info("==== Order Not Found ======");
        }
    }
    private function __deleteProductStorage($item)
    {
        $productStorage = \App\Models\ProductStorage::query()
            ->where([
                'order_id' => $this->orderId,
                'product_id' => $item->product_id,
            ])
            ->first();
        if (!$productStorage) {
            Log::info("Không tìm thấy ProductStorage với order_id={$this->orderId} và product_id={$item->product_id}");
            return; // hoặc xử lý khác nếu cần
        }

        if ($productStorage) {
            $quantity = $productStorage->quantity_change ?? 0;
            $id = $productStorage->id;
            $productStorage->delete();

            $productStorageChange = \App\Models\ProductStorage::query()
                ->where([
                    'user_id' => $this->user->id,
                    'product_id' => $item->product_id,
                ])
                ->where('id', '>', $id)
                ->get();

            foreach ($productStorageChange ?? [] as $value) {
                $value->quantity -= $quantity;
                $value->save();
            }
        }

    }
}
