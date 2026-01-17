<?php
namespace App\Repositories\ProductStorage;

use App\Models\ProductStorage;
use App\Repositories\BaseRepository;
use Carbon\Carbon;

class ProductStorageRepository extends BaseRepository implements ProductStorageRepositoryInterface
{
    protected $model;
    public function getModel()
    {
        return $this->model;
    }
    public function __construct()
    {
        $this->model = new ProductStorage();
    }
    public function getHistoryStorageByProductId($request)
    {
        return $this->model->with([
            'product',
            'order',
            'user'
        ])->where('product_id', $request->product_id)
            ->orderByDesc('id')
            ->paginate($request->get('per_page', 20));
    }
    public function getTotalHistoryStorageByProductId($request)
    {
        return $this->model->with([
            'product',
            'order',
            'user'
        ])->where('product_id', $request->product_id)
            ->count();
    }
    public function getStorageStart($request, $productId)
    {
        $query = $this->model->where('product_id', $productId);
        build_query_by_user_id($query, auth()->user());

        if ($request->start_date) {
            $query->where('created_at', '<', $request->start_date . ' 00:00:00');
        } else {
            $query->where('created_at', '<', Carbon::now()->startOfMonth()->format('Y-m-d 00:00:00'));
        }

        return $query->orderByDesc('id')->first();
    }
    public function getStorageEnd($request, $productId)
    {
        $query = $this->model->where('product_id', $productId);
        build_query_by_user_id($query, auth()->user());

        if ($request->end_date) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        } else {
            $query->where('created_at', '<=', Carbon::today()->format('Y-m-d 23:59:59'));
        }

        return $query->latest()->first();
    }
    public function getStorage($request, $type = 1)
    {
        $query = $this->model
            ->with(['product'])
            ->selectRaw("
                product_id,
                SUM(
                    CASE
                        WHEN type = 2 or (type = 4 and quantity_change > 0)then quantity_change
                        ELSE 0
                    END
                ) AS total_nhap,
                 SUM(
                    CASE
                        WHEN type = 1 or type = 3 then quantity_change
                        ELSE 0
                    END
                ) AS total_xuat,
                 SUM(
                    CASE
                        WHEN type = 4 and quantity_change < 0 then quantity_change
                        ELSE 0
                    END
                ) AS total_can_bang_kho,
                 SUM(
                    CASE
                        WHEN type = 5 then quantity_change
                        ELSE 0
                    END
                ) AS total_khoi_tao_kho
                ");

        build_query_by_user_id($query, auth()->user());

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        } else {
            $query->whereBetween('created_at', [
                Carbon::now()->startOfMonth()->format('Y-m-d 00:00:00'),
                Carbon::today()->format('Y-m-d 23:59:59'),
            ]);
        }

        if ($request->name) {
            $query->whereHas('product', function ($query) use ($request) {
                $name = $request->name;
                $query->where(function ($query) use ($name) {
                    $query->where('name', 'like', "%$name%")->orWhere('code', 'like', "%$name%");
                });
            });
        }

        $query->groupBy('product_id');

        return $query->get();
    }


}
