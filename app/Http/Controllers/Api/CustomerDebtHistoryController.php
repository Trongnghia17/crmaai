<?php

namespace App\Http\Controllers\Api;
use App\Http\Requests\CustomerDebt\StoreRequest;
use App\Http\Resources\CustomerDebt\CustomerDebtResource;
use App\Models\Customer;
use App\Models\CustomerDebtHistory;
use App\Repositories\CustomerDebtHistory\CustomerDebtHistoryRepository;
use App\Repositories\Pagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerDebtHistoryController extends Controller
{
    protected $customerDebtHistoryRepository;

    public function __construct
    (
        CustomerDebtHistoryRepository $customerDebtHistoryRepository,
    )
    {
        $this->customerDebtHistoryRepository = $customerDebtHistoryRepository;
    }
    public function show(Request $request, $id) {
        try {
            $data = $this->customerDebtHistoryRepository->getCustomerDebt($request, $id);
            Log::info('daât 1' . $data);
            $paginate = new Pagination($data);
            return $this->response200(
                CustomerDebtResource::collection($paginate->getItems()),
                $paginate->getMeta(),
            );
        } catch (\Exception $e) {
            Log::error('log 111 ' . $e->getMessage());;
            return $this->response500($e->getMessage());
        }
    }

    public function store(StoreRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $data['customer_id'] = $id;
            $price = $data['price'];

            $customerDebt = Customer::find($id)->where('status', 1)->first();
            if ($price <= 0 || $price > $customerDebt->total_money) {
                return $this->response400('Số tiền không hợp lệ');
            }

            if (!$customerDebt) {
                return $this->response404('Khách hàng không tồn tại');
            }

            if ($price && $customerDebt->total_money > 0) {

                $data['previous_debt'] = $customerDebt->total_money;
                $customerDebt->total_money = $customerDebt->total_money - $price;
                $customerDebt->save();
                $data['remaining_debt'] = $customerDebt->total_money;
                $customer = $this->customerDebtHistoryRepository->create($data);
                DB::commit();
                return $this->response200($customer);
            } else {
                return $this->response400('Khách hàng không có nợ');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('log' . $e->getMessage());;
            return $this->response500($e->getMessage());
        }
    }

    public function repayDebt(Request $request, $id)
    {
        try {
            $customerDebt = CustomerDebtHistory::query()->with(['customer'])->find($id);
            if (!$customerDebt) {
                return $this->response404('Lịch sử thanh toán nợ không tồn tại');
            }
            return view('RepayDebt.repayDebt', [
                'customerDebt' => $customerDebt,
            ]);


        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }
}
