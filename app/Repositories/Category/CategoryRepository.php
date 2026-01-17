<?php

namespace App\Repositories\Category;

use App\Models\Category;
use App\Repositories\BaseRepository;
use App\Repositories\Pagination;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterfae
{

    public function getModel()
    {
        return Category::class;
    }
    public function getCategory($request)
    {
        $size = $request->per_page ?? Pagination::PAGINATION;
        $query = $this->model->query()->where('is_active', 1);
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        build_query_by_user_id($query, auth()->user());
        build_query_sort_field($query, 'id');
        return $query->orderByDesc('id')->paginate($size);
    }

}
