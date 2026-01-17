<?php

namespace App\Http\Controllers\Api;


use App\Http\Resources\ProductStorage\ProductStorageResource;
use App\Models\Product;
use App\Repositories\Pagination;
use App\Repositories\ProductStorage\ProductStorageRepositoryInterface;
use Illuminate\Http\Request;

class ProductStorageController extends Controller
{
    protected $productStorageRepo;
    public function __construct(
        ProductStorageRepositoryInterface $productStorageRepo
    )
    {
        $this->productStorageRepo = $productStorageRepo;
    }

    public function getHistoryStorage(Request $request)
    {
        try{
            if($request->has('product_id')){
                Product::query()->findOrFail($request->product_id);
            }
            $storage = $this->productStorageRepo->getHistoryStorageByProductId($request);
            $total = $this->productStorageRepo->getTotalHistoryStorageByProductId($request);
            $paginate = new Pagination($storage);
            return $this->response200(
                ProductStorageResource::collection($paginate->getItems()),
                $paginate->getMeta(),
                '',
                [
                    'total' => $total
                ]
            );
        }catch (\Exception $exception){
            return $this->response500($exception->getMessage());
        }
    }
}
