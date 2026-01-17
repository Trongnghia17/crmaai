<?php

namespace App\Repositories\CustomerDebtHistory;

use App\Repositories\BaseRepository;
use App\Repositories\CustomerDebtHistory\CustomerDebtHistoryInterface;
use App\Repositories\Pagination;
use Illuminate\Support\Facades\Log;

class CustomerDebtHistoryRepository extends BaseRepository implements CustomerDebtHistoryInterface
{
    public function getModel()
    {
        return \App\Models\CustomerDebtHistory::class;
    }

    public function getCustomerDebt($request, $id)
    {
        $size = $request->per_page ?? Pagination::PAGINATION;
        $query = $this->model->query()->where('customer_id', $id)
            ->with(['user'])
            ->orderByDesc('id');

        Log::info('query' . json_encode($query->toSql()));
        Log::info('query bd' . json_encode($query));
        return $query->paginate($size);
    }
}
