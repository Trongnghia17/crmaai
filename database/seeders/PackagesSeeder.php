<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basicPackages = [
            [
                'name' => 'Gói trải nghiệm',
                'code' => 'TRIAL',
                'days' => '14',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gói sử dụng 3 tháng',
                'code' => 'RE90',
                'days' => '90',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gói sử dụng 6 tháng',
                'code' => 'RE180',
                'days' => '180',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gói sử dụng 12 tháng',
                'code' => 'RE360',
                'days' => '360',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        Package::query()->insert($basicPackages);
    }
}
