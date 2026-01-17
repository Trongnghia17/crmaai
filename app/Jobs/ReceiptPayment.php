<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Supplier;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repositories\ReceiptPayment\ReceiptPaymentRepositoryInterface;
use Illuminate\Support\Facades\Log;

class ReceiptPayment implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Dispatchable, SerializesModels;


    protected $user;
    protected $orderId;
    protected $order;
    protected $price;
    protected $receiptPayment;

    /**
     * Create a new job instance.
     */
    public function __construct($orderId, $user, $price = 0)
    {
        $this->order = app(OrderRepositoryInterface::class);
        $this->receiptPayment = app(ReceiptPaymentRepositoryInterface::class);
        $this->user = $user;
        $this->orderId = $orderId;
        $this->price = $price;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Cập nhật báo cáo và phiếu thu tự động cho đơn hàng ' . $this->orderId . ' số tiền ' . $this->price);

        $order = $this->order->find($this->orderId);
        if ($order->type == 1 || $order->type == 3) {
            $this->receipt($order);
        } else {
            $this->payment($order);
        }
    }
    private function receipt($order)
    {
        $note = 'Phiếu thu tự động';

        $price = $this->price;
        $prefix = 'RE';
        $type = 1;
        if ($order->status == Order::RETURN) {
            $prefix = 'PE';
            $price = $this->price * -1;
            $note = 'Phiếu chi tự động (Trả hàng cho khách)';
            $type = 2;
        }
        $paymentType = 'cash';
        if (count($order->orderPayment) > 0) {
            $orderPayment = $order->orderPayment->last();
            $paymentType = $this->receiptPayment->getPaymentType($orderPayment->type);
        }
        Log::info('22222222222222222222222222222222222222');
        $customer = Customer::query()->find($order->customer_id);
        \App\Models\ReceiptPayment::query()->create([
            "partner_group_id" => 1, // khách hàng
            "partner_group_name" => "khách hàng",
            "partner_id" => @$customer->id,
            "partner_name" => @$customer->name ?? 'Khách lẻ',
            "price" => $price,
            "order_id" => $order->id,
            "receipt_type_id" => 0, // get id tu api loại phiếu phieu thu, 0: auto create
            "payment_type" => $paymentType, // cash, bank, credis,
            "note" => $note,
            "is_other_income" => true,
            'type' => $type,
            "is_edit" => false,
            "code" => $this->getCD($prefix),
            "time" => date('Y-m-d'),
            "user_id" => $order->user_id
        ]);
    }

    private function payment($order)
    {
        $note = 'Phiếu chi tự động';
        $price = '-' . $this->price;
        $prefix = 'PE';
        $type = 2;
        if ($order->is_refund) {
            $prefix = 'RE';
            $price = $this->price;
            $note = 'Phiếu thu tự động (Trả lại hàng nhập)';
            $type = 1;
        }
        $partner_name = "Đại lý";
        $partner_group_name = "Đối tượng khác";
        $partner_id = null;
        $supplier = Supplier::query()->find($order->supplier_id);
        if ($supplier) {
            $partner_name = @$supplier->name;
            $partner_group_name = "Nhà cung cấp";
            $partner_id = @$supplier->id;
        }

        $paymentType = 'cash';
        if (count($order->orderPayment) > 0) {
            $orderPayment = $order->orderPayment->last();
            $paymentType = $this->receiptPayment->getPaymentType($orderPayment->type);
        }

        \App\Models\ReceiptPayment::query()->create([
            "partner_group_id" => 2, // nhà cung cấp
            "partner_group_name" => $partner_group_name,
            "partner_id" => $partner_id,
            "partner_name" => $partner_name,
            "price" => $price,
            "order_id" => $order->id,
            "receipt_type_id" => 0, // get id tu api loại phiếu phieu chi, 0: auto create
            "payment_type" => $paymentType, // cash, bank, cod,
            "note" => $note,
            "is_other_income" => true,
            "type" => $type,
            "code" => $this->getCD($prefix),
            "is_edit" => false,
            "time" => date('Y-m-d'),
            "user_id" => $order->user_id
        ]);
    }
    private function getCD($prefix)
    {
        $row = \App\Models\ReceiptPayment::query()->latest()->first();
        $number = 1;
        if ($row) {
            $number = $row->id + 1;
        }
        return $prefix . str_pad("{$number}", 5, '0', STR_PAD_LEFT);
    }
}
