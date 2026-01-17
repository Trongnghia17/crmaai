<?php

namespace App\Http\Controllers\Api;

use App\Exports\CustomerExport;
use App\Http\Requests\Customer\StoreRequest;
use App\Http\Requests\Customer\UpdateRequest;
use App\Http\Resources\Customer\CustomerCompactResource;
use App\Http\Resources\Customer\CustomerResource;
use App\Models\Customer;
use App\Repositories\Customer\CustomerRepositoryInterface;
use App\Repositories\Pagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    protected $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function store(StoreRequest $request)
    {
        Log::info('Create customer' . json_encode($request->all()));
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $customer = $this->customerRepository->create($data);
            DB::commit();
            return $this->response200($customer);
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function index(Request $request)
    {
        try {
            $data = $this->customerRepository->getCustomer($request);
            $paginate = new Pagination($data);
            return $this->response200(
                CustomerResource::collection($paginate->getItems()),
                $paginate->getMeta(),
            );

        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        Log::info('Update customer' . json_encode($request->all()));
        try {
            DB::beginTransaction();
            $data = $request->validated();
            if (!$this->customerRepository->find($id)) {
                return $this->response404();
            }
            if (isset($data['user_id'])) {
                unset($data['user_id']);
            }
            $customer = $this->customerRepository->update($id, $data);
            DB::commit();
            return $this->response200($customer);
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function detail(Request $request, $id)
    {
        try {
            $customer = $this->customerRepository->find($id);
            if (!$customer) {
                return $this->response404();
            }
            return $this->response200(new CustomerCompactResource($customer));

        } catch(\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $customer = $this->customerRepository->find($id);
            if (!$customer) {
                return $this->response404();
            }
            $this->customerRepository->update($id, ['status' => 0]);
            DB::commit();
            return $this->response200();
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function exportCustomer(Request $request)
    {
        try {
            $customers = Customer::query()->where('user_id', auth()->id())->get();
            $dataPrint = [
              'customers' => $customers,
              'user' => auth()->user(),
            ];
            return Excel::download(new CustomerExport($dataPrint), 'khach_hang.xlsx');
        } catch (\Exception $exception) {
            return $this->response500($exception->getMessage());
        }
    }
}
