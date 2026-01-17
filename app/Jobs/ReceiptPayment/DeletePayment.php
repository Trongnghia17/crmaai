<?php

namespace App\Jobs\ReceiptPayment;

use App\Models\Aggregate;
use App\Models\ProfitLoss;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeletePayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $receipt;

    /**
     * Create a new job instance.
     */
    public function __construct($receipt, $user)
    {
        $this->receipt = $receipt;
        $this->user = $user;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info($this->receipt);
        $time = $this->receipt->time;
        $price = $this->receipt->price;
        $userId = $this->receipt->user_id;
        // update profit
        $now = date('Y-m-d');
        $time = date('Y-m-d', strtotime($time));

        if ($this->receipt->is_other_income) {
            $profit = ProfitLoss::query()->where([
                'user_id' => $userId,
                'time' => $time,
            ])->first();

            if ($profit) {
                $profit->other_cost -= $price;
                $profit->save();
            }
        }


        // update bao cao cuối kỳ
        $datetime1 = new \DateTime($now);
        $datetime2 = new \DateTime($time);
        $interval = $datetime1->diff($datetime2);
        $days = $interval->format('%a');

        for ($i = 0; $i < $days; $i ++) {
            $start = Carbon::make($time)->addDays($i)->format('Y-m-d');

            $aggregate = Aggregate::query()
                ->where('user_id', $userId)
                ->where('time', $start)
                ->first();

            if ($aggregate) {
                $aggregate->total -= $price;
                $aggregate->save();
            }
        }
    }
}
