<?php

namespace App\Http\Controllers\Api;

use App\Exports\ProductExport;
use App\Http\Requests\Product\ImportRequest;
use App\Http\Requests\Product\ListProductRequest;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\Product\ProductResource;
use App\Imports\ProductImport;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Pagination;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Product\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductsController extends Controller
{
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->productRepository->getProduct($request);
            $paginate = new Pagination($data);
            return $this->response200(
                ProductResource::collection($paginate->getItems()),
                $paginate->getMeta(),
            );
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function indexV2(ListProductRequest $request)
    {
        $name = $request->input('name');
        $user_id = auth()->id();
        $perPage = $request->input('per_page', 20);

        $query = Product::query()
            ->where('is_active', true)
            ->where('is_show', true)
            ->where('user_id', $user_id);

        if ($name) {
            $query->where(function($q) use ($name) {
                $q->where('name', 'LIKE', '%' . $name . '%')
                  ->orWhere('sku', 'LIKE', '%' . $name . '%')
                  ->orWhere('barcode', 'LIKE', '%' . $name . '%');
            });
        }

        $paginated = $query->orderByDesc('id')->paginate($perPage);

        $paginate = new Pagination($paginated);

        return $this->response200(
            ProductResource::collection($paginate->getItems()),
            $paginate->getMeta()
        );
    }


    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $imageName = Str::slug(pathinfo($request->file('image')->getClientOriginalName(), PATHINFO_FILENAME));

                $imageName = $imageName . '-' . time() . '.' . $request->file('image')->getClientOriginalExtension();
                $imagePath = $request->file('image')->storeAs('products', $imageName, 'public');
                $data['image'] = $imagePath;
            }
            Log::info('data' . json_encode($data));
            $data['user_id'] = auth()->id();
            $data['entry_cost'] = $data['base_cost'] ?? 0;
            if (empty($data['sku'])) {
                $data['sku'] = $this->productRepository->getSkuProduct();
            }
            $product = $this->productRepository->create($data);
            $product->category()->sync(\Arr::wrap($request->input('category_ids')));
            $product->save();
            DB::commit();
            return $this->response200(new ProductResource($product));
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function show($id)
    {
        return $this->productRepository->find($id);
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            Log::info('request' . json_encode($request->all()));
            DB::beginTransaction();
            $data = $request->validated();
            Log::info('data' . json_encode($data));

            $product = $this->productRepository->find($id);
            if (!$product) {
                return $this->response404();
            }

            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu tồn tại
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                // Lưu ảnh mới
                $imageName = Str::slug(pathinfo($request->file('image')->getClientOriginalName(), PATHINFO_FILENAME));
                $imageName = $imageName . '-' . time() . '.' . $request->file('image')->getClientOriginalExtension();
                $imagePath = $request->file('image')->storeAs('products', $imageName, 'public');
                $data['image'] = $imagePath;
            }
            $product->category()->sync(\Arr::wrap($request->input('category_ids')));
            $product = $this->productRepository->update($id, $data);
            DB::commit();
            return $this->response200($product);
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $product = $this->productRepository->find($id);
            if (!$product) {
                return $this->response404();
            }
            $product->is_active = 0;
            $product->save();
            DB::commit();
            return $this->response200('Xóa sản phẩm thành công!');
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function import(ImportRequest $request)
    {
        try {
            Excel::import(new ProductImport(), $request->file('file')->store('temp'));
            return $this->response200('Import sản phẩm thành công!');

        } catch (\Exception $e) {
            return $this->response500($e);
        }
    }

    public function export(Request $request)
    {
        Log::info('request' . json_encode($request->all()));
        $dataPrint = [];
        $from = $request->input('from') ? Carbon::parse($request->input('from'))->format('Y-m-d') : null;
        $to = $request->input('to') ? Carbon::parse($request->input('to'))->format('Y-m-d') : null;
        if ($from && $to) {
            if ($from > $to) {
                return $this->response422('Ngày bắt đầu không được lớn hơn ngày kết thúc');
            }
        }

        $userId = auth()->id();
        $products = Product::query()->where('is_active', true)->where('user_id', $userId);
        if ($from && $to) {
            $products->whereBetween('created_at', [$from, $to]);
        }
        $user = User::query()->where('id', $userId)->first();

        $product = $products->get();
        $dataPrint = [
            'products' => $product,
            'from' => $from,
            'to' => $to,
            'user' => $user,
        ];
        return Excel::download(new ProductExport($dataPrint), 'San_pham.xlsx');
    }
}
