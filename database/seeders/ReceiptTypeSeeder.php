<?php

namespace Database\Seeders;

use App\Models\ReceiptType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReceiptTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ReceiptType::query()->insert(
            [
                [
                    'name' => 'Tiền bồi thường',
                    'type' => 1,
                    'status' => 1,
                    'user_id' => 0,
                ],
                [
                    'name' => 'Thu nợ khách hàng',
                    'type' => 1,
                    'status' => 1,
                    'user_id' => 0,
                ],
                [
                    'name' => 'Thu nhập khác',
                    'type' => 1,
                    'status' => 1,
                    'user_id' => 0,
                ],
                [
                    'name' => 'Nhượng bán thanh lý tài sản',
                    'type' => 1,
                    'status' => 1,
                    'user_id' => 0,
                ],
                [
                    'name' => 'Cho thuê tài sản',
                    'type' => 1,
                    'status' => 1,
                    'user_id' => 0,
                ],
                [
                    'name' => 'Đối tác vận chuyển đặt cọc',
                    'type' => 1,
                    'status' => 1,
                    'user_id' => 0,
                ],
                [
                    'name' => 'Thanh toán cho đơn nhập hàng',
                    'type' => 2,
                    'status' => 1,
                    'user_id' => 0,
                ],
                [
                    'name' => 'Chi phí sinh hoạt',
                    'type' => 2,
                    'status' => 1,
                    'user_id' => 0,
                ],
                [
                    'name' => 'Chi phí sản xuất',
                    'type' => 2,
                    'status' => 1,
                    'user_id' => 0,
                ],
                [
                    'name' => 'Chi phí quản lý cửa hàng',
                    'type' => 2,
                    'status' => 1,
                    'user_id' => 0,
                ],
                [
                    'name' => 'Chi phí nhân công',
                    'type' => 2,
                    'status' => 1,
                    'user_id' => 0,
                ],
                [
                    'name' => 'Chi phí nguyên vật liệu',
                    'type' => 2,
                    'status' => 1,
                    'user_id' => 0,
                ],
                [
                    'name' => 'Chi phí khác',
                    'type' => 2,
                    'status' => 1,
                    'user_id' => 0,
                ],
                [
                    'name' => 'Chi phí bán hàng',
                    'type' => 2,
                    'status' => 1,
                    'user_id' => 0,
                ],
            ]
        );
    }
}
