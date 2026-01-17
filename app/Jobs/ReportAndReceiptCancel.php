<?php

namespace App\Jobs;

use App\Models\Aggregate;
use App\Repositories\Order\OrderRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReportAndReceiptCancel implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected $user;
    protected $orderId;
    protected $order;
    protected $price;
    protected $time;
    /**
     * Create a new job instance.
     */
    public function __construct($orderId, $user, $price = 0, $time)
    {
        $this->order = app(OrderRepositoryInterface::class);
        $this->user = $user;
        $this->orderId = $orderId;
        $this->price = $price;
        $this->time = $time;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = $this->order->find($this->orderId);
        // tạo phiếu thu chi
        \App\Models\ReceiptPayment::query()->where('order_id', $order->id)->delete();

        // update profit
        $now = date('Y-m-d');
        $time = date('Y-m-d', strtotime($order->updated_at));
        $vatRetail = $order->retail_cost - ($order->retail_cost * 100 / (100 + $order->vat));
        $vatService = $order->service_fee - ($order->service_fee * 100 / (100 + $order->vat));
        $profit = \App\Models\ProfitLoss::query()->where([
            'user_id' => $order->user_id,
            'time' => $this->time,
        ])->first();

        if ($profit) {
            $profit->revenue_sale -= $order->retail_cost_base + $order->service_fee;
            $profit->discount_sale -= $order->retail_cost_base - $order->retail_cost + $vatRetail + $vatService;
            $profit->cost_sale -= $order->entry_cost;
            $profit->vat -= $vatRetail + $vatService;
            $profit->save();
        }

        // update bao cao cuối kỳ
        $datetime1 = new \DateTime($now);
        $datetime2 = new \DateTime($this->time);
        $interval = $datetime1->diff($datetime2);
        $days = $interval->format('%a');

        for ($i = 0; $i < $days; $i++) {
            $start = Carbon::make($this->time)->addDays($i)->format('Y-m-d');

            $aggregate = Aggregate::query()
                ->where('user_id', $order->user_id)
                ->where('time', $start)
                ->first();

            if ($aggregate) {
                $aggregate->total -= $this->price;
                $aggregate->save();
            }
        }
    }
}
