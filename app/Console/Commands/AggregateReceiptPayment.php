<?php

namespace App\Console\Commands;

use App\Models\Aggregate;
use App\Models\ReceiptPayment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AggregateReceiptPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:aggregate-receipt-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Summarize the total amount of receipts and payments for each user on a daily basis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $receiptPayments = ReceiptPayment::query()
            ->select('user_id', DB::raw('sum(price) as total'))
            ->where('status', ReceiptPayment::STATUS_SUCCESS)
            ->whereBetween('time', [
                Carbon::yesterday()->format('Y-m-d 00:00:00'),
                Carbon::yesterday()->format('Y-m-d 23:59:59')
            ])
            ->groupBy('user_id')
            ->get();

        foreach ($receiptPayments ?? [] as $receiptPayment) {
            $lastRecord = Aggregate::query()
                ->where('user_id', $receiptPayment->user_id)
                ->latest('id')->first();

            $total = 0;
            if ($lastRecord) {
                $total = $lastRecord->total;
            }

            Aggregate::query()->create([
                'user_id' => $receiptPayment->user_id,
                'date' => Carbon::yesterday()->format('Y-m-d'),
                'total' => $total + $receiptPayment->total,
                'time' => Carbon::yesterday()->format('Y-m-d'),
            ]);
        }
    }
}
