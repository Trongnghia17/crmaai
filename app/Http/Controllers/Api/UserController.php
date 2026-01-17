<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\User\UpdateStoreRequest;
use App\Http\Resources\Product\ProductResource;
use App\Repositories\Pagination;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\UserRepository\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    protected $productRepository;
    protected $userRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        UserRepositoryInterface $userRepository,
    )
    {
        $this->productRepository = $productRepository;
        $this->userRepository = $userRepository;
    }

    public function getStorage(Request $request)
    {
        try {
            Log::info('Get storage' . json_encode($request->all()));;


            $product = $this->productRepository->getProduct($request);
            $paginate = new Pagination($product);
            $total = [
                'base_cost' => (float)$this->productRepository->getSum($request, 'base_cost'),
                'available' => (int)$this->productRepository->getSum($request, 'available'),
                'in_stock' => (int)$this->productRepository->getSum($request, 'in_stock'),
            ];

            return $this->response200(
                ProductResource::collection($paginate->getItems()),
                $paginate->getMeta(),
                null,
                $total
            );

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->response500($e->getMessage());

        }

    }



}
