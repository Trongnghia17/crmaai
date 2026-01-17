<?php
// filepath: /home/admin12/PhpstormProjects/SaleManagement/SaleManagement/app/Jobs/OrderStorage.php
namespace App\Jobs;

use App\Models\Order;
use App\Models\ProductStorage;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OrderStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;

    protected $orderId;
    protected $userId;
    protected $type;
    protected $action;

    /**
     * Create a new job instance.
     */
    public function __construct($orderId, $userId, $type, $action = 'create')
    {
        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->type = $type;
        $this->action = $action;
    }

    /**
     * Execute the job.
     */
    public function handle(): void

    {
        Log::info("==== Start Product Storage 1======");;
        try {
            $user = User::find($this->userId);
            $order = Order::with('orderDetail.product')->find($this->orderId);

            if (!$order) {
                Log::info("Order not found with ID: {$this->orderId}");
                return;
            }

            foreach ($order->orderDetail ?? [] as $item) {
                $this->__insertProductStorage($item, $order->status == Order::RETURN , $user);
            }

        } catch (\Exception $e) {
            Log::error("Error in OrderStorage job: " . $e->getMessage());
        }
    }

    private function __insertProductStorage($item, $isRefund, $user): void
    {
        try {
            $data = [
                'product_id' => $item->product_id,
                'user_id' => $user->id,
                'type' => $this->type,
                'quantity' => $item->product->available ?? 0,
                'order_id' => $this->orderId,
            ];

            if ($this->type == 1) {
                $data['quantity_change'] = $isRefund ? $item->quantity : -$item->quantity;
            } else if ($this->type == 2) {
                $data['quantity_change'] = $isRefund ? -$item->quantity : $item->quantity;
            }
            Log::info("Product Storage Data: ", $data);
            ProductStorage::create($data);
        } catch (\Exception $e) {
            Log::error("Error inserting product storage: " . $e->getMessage());
        }
    }
}
