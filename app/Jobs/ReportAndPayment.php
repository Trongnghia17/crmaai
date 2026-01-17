<?php

namespace App\Jobs;

use App\Models\Aggregate;
use App\Models\Supplier;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\ReceiptPayment\ReceiptPaymentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReportAndPayment implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $user;
    protected $orderId;
    protected $order;
    protected $price;


    /**
     * Create a new job instance.
     */
    public function __construct($orderId, $user, $price = 0)
    {
        $this->order = app(OrderRepositoryInterface::class);
        $this->user = $user;
        $this->orderId = $orderId;
        $this->price = $price;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = $this->order->find($this->orderId);
        $now = date('Y-m-d');
        $time = date('Y-m-d', strtotime($order->created_at));
        $note = 'Phiếu chi tự động';
        $price = '-' . $this->price;

        $partner_name = "Đại lý";
        $partner_group_name = "Đối tượng khác";
        $partner_id = null;
        $supplier = Supplier::query()
            ->where('id', $order->supplier_id)
            ->where('user_id', $order->user_id)
            ->first();
        if ($supplier) {
            $partner_name = @$supplier->name;
            $partner_group_name = "Nhà cung cấp";
            $partner_id = @$supplier->id;
        }

        $paymentType = 'cash';
        if (count($order->orderPayment) > 0) {
            $orderPayment = $order->orderPayment->last();
            $paymentType = app(ReceiptPaymentRepositoryInterface::class)::getPaymentType($orderPayment->type);
        }

        // Tạo phiếu chi
        if ($price != 0) {
            \App\Models\ReceiptPayment::query()->create([
                "partner_group_id" => 1, // nhà cung cấp
                "partner_group_name" => $partner_group_name,
                "partner_id" => $partner_id,
                "partner_name" => $partner_name,
                "price" => $price,
                "order_id" => $order->id,
                "receipt_type_id" => 0, // get id tu api loại phiếu phieu chi, 0: auto create
                "payment_type" => $paymentType, // cash, bank, cod,
                "note" => $note,
                "is_other_income" => true,
                "type" => 2,
                "code" => $this->getCD('PE'),
                "is_edit" => false,
                "time" => $time,
                "user_id" => $order->user_id
            ]);
        }

        // update bao cao cuối kỳ
        $datetime1 = new \DateTime($now);
        $datetime2 = new \DateTime($time);
        $interval = $datetime1->diff($datetime2);
        $days = $interval->format('%a');
        $date = Carbon::now()->subDays($days)->format('Y-m-d');

        for ($i = 0; $i < $days; $i++) {
            $start = Carbon::make($time)->addDays($i)->format('Y-m-d');

            $aggregate = Aggregate::query()
                ->where('user_id', $order->user_id)
                ->where('time', $start)
                ->first();

            if ($aggregate) {
                $aggregate->total -= $this->price;
                $aggregate->save();
            } else if ($i == 0) { // chi phải tạo 1 bản ghi của ngày đầu tiên, những ngày sau có thì - ko có thì thôi
                $lastRecord = Aggregate::query()
                    ->where('user_id', $order->user_id)
                    ->where('time', '<', $date)
                    ->latest('time')->first();

                $total = 0;
                if ($lastRecord) {
                    $total = $lastRecord->total;
                }

                Aggregate::query()->create([
                    'user_id' => $order->user_id,
                    'date' => $time,
                    'total' => $total - $this->price,
                    'time' => $time
                ]);
            }
        }
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
