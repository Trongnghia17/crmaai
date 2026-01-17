<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeductUsageDays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deduct-usage-days';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trừ đi 1 ngày sử dụng vào lúc 12h đêm mỗi ngày';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        User::where('remaining_days', '>', 0)
            ->update([
                'remaining_days' => \DB::raw('remaining_days - 1'),
            ]);
        $this->info('Đã trừ 1 ngày sử dụng cho tất cả người dùng.');
    }
}
