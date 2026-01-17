<?php

namespace App\Repositories\Customer;

use App\Models\Customer;
use App\Repositories\BaseRepository;
use App\Repositories\Pagination;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{

    public function getModel()
    {
        return Customer::class;
    }

    public function getCustomer($request)
    {
        $size = $request->per_page ?? Pagination::PAGINATION;
        $query = $this->model->query()->where('status', 1);
        if($request->has('search')) {
            $query->where(function ($query) use($request) {
                $query->where('name', 'like', "%$request->search%")
                    ->orWhere('phone', 'like', "%$request->search%")
                    ->orWhere('email', 'like', "%$request->search%");
            });
        }
        build_query_by_user_id($query, auth()->user());
        build_query_sort_field($query, $request->sort ?? '');
        return $query->orderByDesc('id')->paginate($size);
    }


    public function revenueByOrderNull(Request $request)
    {
        $query = $this->model->where('user_id', auth()->id());
//            ->whereNotExists(function($query) {
//                $query->select(DB::raw(1))
//                    ->from('orders')
//                    ->WhereRaw('orders.customer_id = customers.id');
//            });

        if ($request->name) {
            $query->where(function ($query) use($request) {
                $query->where('name', 'like', "%$request->name%")
                    ->orWhere('phone', 'like', "%$request->name%");
            });
        }

        build_query_by_user_id($query, auth()->user());
        \Log::info('User ID: ' . auth()->user()->id);
        \Log::info('query sql ' . json_encode($query->toSql()));
        \Log::info('query bd' . json_encode($query->getBindings()));
        $result = $query->get();
        \Log::info('query' . json_encode($result->toArray()));;
        return $query->get();
    }

}
