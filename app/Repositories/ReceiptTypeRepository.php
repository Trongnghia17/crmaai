<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\ReceiptType;
use Illuminate\Http\Request;

class ReceiptTypeRepository extends BaseRepository
{
    protected $model;

    public function getModel()
    {
        return ReceiptType::class;
    }
    public function __construct()
    {
        parent::__construct();
    }

    public function getAllType(Request $request)
    {
        $query = $this->model;

        if ($request->has('name')) {
            $query = $query->where('name', 'like', "%$request->name%");
        }

        if ($request->has('type')) {
            $query = $query->where('type', $request->type);
        }

        $query = $query->where('status', true);

        $query->where(function ($query) {
            build_query_by_user_id($query, auth()->user());
            $query->orWhere('user_id', 0);
        });

        build_query_sort_field($query, $request->get('sort', 'created_at'));

        return $query->paginate($request->get('per_page', 20));
    }

    public function getData(Request $request): array
    {
        return [
            'name' => $request->name,
            'status' => true,
            'type' => $request->get('type', 1),
            'user_id' => auth()->id(),
        ];
    }
}
