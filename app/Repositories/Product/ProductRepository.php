<?php

namespace App\Repositories\Product;

use App\Models\Product;
use App\Repositories\BaseRepository;
use App\Repositories\Pagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{

    public function getModel()
    {
        return Product::class;
    }

    public function getProduct($request)
    {
        Log::info('request get product' . json_encode($request->all()));
        $size = $request->per_page ?? Pagination::PAGINATION;
        $query = $this->model->query()->where('is_active', true);
        if ($request->has('name') && !empty($request->name)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%')
                    ->orWhere('sku', 'like', '%' . $request->name . '%');
            });
        }
        if ($request->has('category_id')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('category.id', $request->category_id);
            });
        }
        if ($request->has('base_cost')) {
            $query->where('base_cost', 'like', '%' . $request->base_cost . '%');
        }
        if ($request->has('retail_cost')) {
            $query->where('retail_cost', 'like', '%' . $request->retail_cost . '%');
        }
        if ($request->has('wholesale_cost')) {
            $query->where('wholesale_cost', 'like', '%' . $request->wholesale_cost . '%');
        }
        if ($request->has('in_stock')) {
            $query->where('in_stock', 'like', '%' . $request->in_stock . '%');
        }
        if ($request->has('sold')) {
            $query->where('sold', 'like', '%' . $request->sold . '%');
        }
        if ($request->has('temporality')) {
            $query->where('temporality', 'like', '%' . $request->temporality . '%');
        }
        if ($request->has('available')) {
            $query->where('available', 'like', '%' . $request->available . '%');
        }
        build_query_by_user_id($query, auth()->user());
        build_query_sort_field($query, $request->sort ?? 'updated_at');
        return $query->orderByDesc('id')->paginate($size);
    }

    public function getSum(Request $request, $field = 'is_stock')
    {
        $query = $this->model;

        $this->viewQuery($request, $query);

        return $query->sum($field);
    }

    private function viewQuery($request, &$query)
    {
        $name = $request->input('name');
        $query->where('is_active', true);
        build_query_by_user_id($query, auth()->user());
        if ($name) {
            $query->where(function ($query) use ($name) {
                $query->where('sku', 'like', "%$name%")
                    ->orWhere('name', 'like', "%$name%");
            });
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->get('start_date') . ' 00:00:00',
                $request->get('end_date') . ' 23:59:59',
            ]);
        }
        return $query;
    }

    public function getSkuProduct()
    {
        $userId = auth()->id();

        do {
            $randomNumber = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $sku = 'SP' . $userId . $randomNumber;

            // Kiểm tra trùng SKU trong CSDL với user_id hiện tại
            $exists = \App\Models\Product::where('user_id', $userId)
                ->where('sku', $sku)
                ->exists();
        } while ($exists);

        return $sku;
    }

    public function formatProducts($query)
    {
        $products = [];

        foreach ($query as $product) {
            $productId = $product->id;

            if (!isset($products[$productId])) {
                $category = $product->category
                    ? [['id' => $product->category->id, 'name' => $product->category->name]]
                    : [];
                $products[$productId] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->sku,
                    'barcode' => $product->barcode,
                    'image' => json_decode($product->image),
                    'unit' => $product->unit,
                    'category' => $category,
                ];
            }

            $productKey = array_search($product->id, array_column($products[$productId], 'id'));

            if ($productKey === false) {
                $products[$productId][] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->image,
                    'sku' => $product->sku,
                    'in_stock' => $product->in_stock,
                    'available' => $product->available,
                    'temporality' => $product->temporality,
                    'base_cost' => $product->base_cost,
                    'barcode' => $product->barcode,
                    'retail_cost' => $product->retail_cost,
                    'wholesale_cost' => $product->wholesale_cost,
                    'entry_cost' => $product->entry_cost,
                    'is_buy_alway' => (bool) $product->is_buy_alway,
                    'discount_type' => $product->discount_type,
                    'discount' => $product->discount,
                ];
                $productKey = count($products[$productId]) - 1;
            }

        }

        return $products;
    }
}
