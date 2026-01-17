<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests\Inventory\DetailRequest;
use App\Http\Requests\Inventory\StoreRequest;
use App\Http\Resources\Inventory\InventoryResource;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\InventoryDetail\InventoryDetailRepositoryInterface;
use App\Repositories\Pagination;
use App\Repositories\Product\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class InventoryController extends Controller
{
    protected $inventoryRepo;

    protected $inventoryDetailRepo;

    protected $productRepo;

    public function __construct(
        InventoryDetailRepositoryInterface $inventoryDetailRepo,
        InventoryRepositoryInterface $inventoryRepo,
        ProductRepositoryInterface $productRepo,
    )
    {

        $this->inventoryRepo = $inventoryRepo;
        $this->inventoryDetailRepo = $inventoryDetailRepo;
        $this->productRepo = $productRepo;
    }

    public function index(Request $request) {
        $inventories = $this->inventoryRepo->getList($request);
        $result = new Pagination($inventories);
        return $this->response200(
            InventoryResource::collection($result->getItems()),
            $result->getMeta()
        );
    }

    public function create(StoreRequest $request)
    {
        Log::channel('inventory')->info(json_encode($request->all()));
        try {
            DB::beginTransaction();
            $data = $this->inventoryRepo->getData($request);
            $inventory = $this->inventoryRepo->create($data);
            $inventoryDetail = $this->inventoryDetailRepo->getData($request, $inventory->id);
            $inventory->inventoryDetail()->saveMany($inventoryDetail);

//            dispatch(new InventoryDetailBatch($inventory->id, $request->inventories_detail));
            DB::commit();
            return $this->response200();
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->response500($exception->getMessage());
        }
    }

    public function detail(Request $request, $id)
    {
        $inventory = $this->inventoryRepo->find($id);
        if (!$inventory) {
            return $this->response404('Không tim thấy phiếu kiểm kho');
        }
        return $this->response200(
            new InventoryResource($inventory)
        );
    }

    public function delete(DetailRequest $request, $id)
    {
        $inventory = $this->inventoryRepo->find($id);
        if ($inventory->status) {
            return $this->response422('Phiếm kiểm kho này không được xóa');
        }
        $this->inventoryRepo->delete($id);
        return $this->response200();
    }


}
