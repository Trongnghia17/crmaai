<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        if (empty($row['sku']) && empty($row['barcode'])) {
            return null;
        }

        $sku = $row['sku'] ?? null;
        $barcode = $row['barcode'] ?? null;

        $query = Product::query();

        if (!empty($sku)) {
            $query->orWhere('sku', $sku);
        }

        if (!empty($barcode)) {
            $query->orWhere('barcode', $barcode);
        }

        if ($query->exists()) {
            return null;
        }

        return new Product([
            'user_id' => auth()->user()->id,
            'name' => $row['ten_san_pham'],
            'image' => $row['image'] ?? null,
            'description' => $row['mo_ta_san_pham'] ?? null,
            'is_active' => 1,
            'is_buy_always' => $row['san_pham_ban_am'] ?? 0,
            'sku' => $row['sku'] ?? null,
            'base_cost' => $row['gia_nhap'] ?? 0,
            'retail_cost' => $row['gia_ban_le'] ?? 0,
            'wholesale_cost' => $row['gia_ban_buon'] ?? 0,
            'entry_cost' => 0,
            'in_stock' => $row['so_luong'],
            'sold' => $row['sold'] ?? 0,
            'temporality' => $row['so_luong'] ?? 0,
            'available' => $row['available'] ?? 0,
            'unit' => $row['don_vi'] ?? null,
            'barcode' => $row['barcode'] ?? null,
        ]);
    }
}
