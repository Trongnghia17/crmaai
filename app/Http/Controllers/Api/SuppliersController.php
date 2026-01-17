<?php

namespace App\Http\Controllers\Api;

use App\Exports\SupplierExport;
use App\Http\Requests\Supplier\StoreRequest;
use App\Http\Requests\Supplier\UpdateRequest;
use App\Http\Resources\Supplier\SupplierResource;
use App\Models\Supplier;
use App\Repositories\Pagination;
use App\Repositories\Supplier\SupplierRepository;
use App\Repositories\Supplier\SupplierRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SuppliersController extends Controller
{
    protected $supplierRepository;

    public function __construct(SupplierRepositoryInterface $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    public function store(StoreRequest $request)
    {
        Log::info('Create supplier' . json_encode($request->all()));
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $supplier = $this->supplierRepository->create($data);
            DB::commit();
            return $this->response200($supplier);
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function index(Request $request)
    {
        try {
            $data = $this->supplierRepository->getSupplier($request);
            $paginate = new Pagination($data);
            return $this->response200(
                SupplierResource::collection($paginate->getItems()),
                $paginate->getMeta(),
            );

        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        Log::info('Update supplier' . json_encode($request->all()));
        try {
            DB::beginTransaction();
            $data = $request->validated();
            if (!$this->supplierRepository->find($id)) {
                return $this->response404('Supplier not found');
            }
            $this->supplierRepository->update($id, $data);
            DB::commit();
            return $this->response200('Update supplier success');
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function delete($id)
    {
        Log::info('Delete supplier' . json_encode($id));
        try {
            DB::beginTransaction();
            if (!$this->supplierRepository->find($id)) {
                return $this->response404('Supplier not found');
            }
            $this->supplierRepository->delete($id);
            DB::commit();
            return $this->response200('Delete supplier success');
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function exportSupplier(Request $request)
    {
        try {
            $suppliers = Supplier::query()->where('user_id', auth()->id())->get();
            $dataPrint = [
                'suppliers' => $suppliers,
                'user' => auth()->user(),
            ];
            return Excel::download(new SupplierExport($dataPrint), 'nha_cung_cap.xlsx');
        } catch (\Exception $exception) {
            return $this->response500($exception->getMessage());
        }
    }
}
