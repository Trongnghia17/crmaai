<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subscriptions;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'nghia@gmail.com',
            'type' => 1, // Admin hoặc user không cần subscription check
        ]);

        // Tạo subscription cho user (nếu type = 2)
        // Subscriptions::create([
        //     'user_id' => $user->id,
        //     'start_date' => Carbon::now(),
        //     'end_date' => Carbon::now()->addDays(30),
        //     'remaining_days' => 30,
        // ]);
    }
}
