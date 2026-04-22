<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // ✅ Tạo company trước (để test GPS)
        $company = Company::create([
            'name' => 'Công ty Novaedu',
            'lat' => 21.021892, // Hà Nội
            'lng' => 105.816603,
            'radius' => 200
        ]);

        // ✅ Tạo 3 user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
            'company_id' => $company->id
        ]);

        User::create([
            'name' => 'Nhân viên 1',
            'email' => 'user1@gmail.com',
            'password' => Hash::make('123456'),
            'company_id' => $company->id
        ]);

        User::create([
            'name' => 'Nhân viên 2',
            'email' => 'user2@gmail.com',
            'password' => Hash::make('123456'),
            'company_id' => $company->id
        ]);
    }
}
