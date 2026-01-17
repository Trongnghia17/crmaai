<?php

namespace App\Jobs;

use App\Models\Aggregate;
use App\Models\Customer;
use App\Models\ProfitLoss;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\ReceiptPayment\ReceiptPaymentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReportAndReceipt implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Dispatchable, SerializesModels;

    protected $user;
    protected $orderId;
    protected $order;
    protected $price;

    /**
     * Create a new job instance.
     *
     * @return void
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
        $note = 'Phiếu thu tự động';

        $price = $this->price;
        \Log::info('Cập nhật báo cáo và phiếu thu tự động cho đơn hàng ' . $order->id . ' số tiền ' . $price);

        $paymentType = 'cash';
        if (count($order->orderPayment) > 0) {
            $orderPayment = $order->orderPayment->last();
            $paymentType = app(ReceiptPaymentRepositoryInterface::class)::getPaymentType($orderPayment->type);
        }

        $customer = Customer::query()
            ->where('phone', $order->phone)
            ->where('user_id', $order->user_id)
            ->first();

        // tạo phiếu thu chi
        if ($price != 0) {
            \Log::info('Tạo phiếu thu tự động');
            \App\Models\ReceiptPayment::query()->create([
                "partner_group_id" => 2, //khách hàng
                "partner_group_name" => "khách hàng",
                "partner_id" => @$customer->id,
                "partner_name" => $order->name,
                "price" => $price,
                "order_id" => $order->id,
                "receipt_type_id" => 0, // get id tu api loại phiếu phieu thu, 0: auto create
                "payment_type" => $paymentType, // cash, bank, credis,
                "note" => $note,
                "is_other_income" => true,
                "is_edit" => false,
                "code" => $this->getCD('RE'),
                "time" => date('Y-m-d', strtotime($order->created_at)),
                "user_id" => $order->user_id,
            ]);
        }
        // update profit
        $now = date('Y-m-d');
        $time = date('Y-m-d', strtotime($order->created_at));
        $vatRetail = $order->retail_cost - ($order->retail_cost * 100 / (100 + $order->vat));
        $profit = ProfitLoss::query()->where([
            'user_id' => $order->user_id,
            'time' => $time,
        ])->first();

        if ($profit) {
            $profit->revenue_sale += $order->retail_cost_base;
            $profit->discount_sale += $order->retail_cost_base - $order->retail_cost + $vatRetail;
            $profit->cost_sale += $order->entry_cost;
            $profit->vat += $vatRetail;
            $profit->save();
        } else {
            ProfitLoss::query()->create([
                'user_id' => $order->user_id,
                'revenue_sale' => $order->retail_cost_base,
                'discount_sale' => $order->retail_cost_base - $order->retail_cost + $vatRetail,
                'cost_sale' => $order->entry_cost,
                'vat' => $vatRetail,
                'fee' => 0,
                'order_cancel' => 0,
                'time' => $time,
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
                $aggregate->total += $price;
                $aggregate->save();
            } else if ($i == 0) { // chi phải tạo 1 bản ghi của ngày đầu tiên, những ngày sau có thì + ko có thì thôi
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
                    'total' => $total + $price,
                    'time' => $time,
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
