<?php

namespace App\Repositories\Supplier;

use App\Models\Supplier;
use App\Repositories\BaseRepository;
use App\Repositories\Pagination;

class SupplierRepository extends BaseRepository implements SupplierRepositoryInterface
{

    public function getModel()
    {
        return Supplier::class;
    }

    public function getSupplier($request)
    {
        $size = $request->per_page ?? Pagination::PAGINATION;
        $query = $this->model->query()->where('status', 1);
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->has('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }
        if ($request->has('address')) {
            $query->where('address', 'like', '%' . $request->address . '%');
        }
        build_query_by_user_id($query, auth()->user());
        build_query_sort_field($query, $request->sort ?? '');
        return $query->orderByDesc('id')->paginate($size);
    }
}
