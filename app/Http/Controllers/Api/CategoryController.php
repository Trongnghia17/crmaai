<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Category\CreateRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Http\Resources\Category\CategoryResource;
use App\Repositories\Category\CategoryRepositoryInterfae;
use App\Repositories\Pagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    protected $categoryRepository;

    public function __construct(CategoryRepositoryInterfae $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function create(CreateRequest $request)
    {
        Log::info('Create category' . json_encode($request->all()));
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $category = $this->categoryRepository->create($data);
            DB::commit();
            return $this->response200($category);
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function index(Request $request)
    {
        try {
            $data = $this->categoryRepository->getCategory($request);
            $paginate = new Pagination($data);
            return $this->response200(
                CategoryResource::collection($paginate->getItems()),
                $paginate->getMeta(),
            );
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        Log::info('Update category' . json_encode($request->all()));
        try {
            DB::beginTransaction();
            $data = $request->validated();
            if (!$this->categoryRepository->find($id)) {
                return $this->response404();
            }
            $category = $this->categoryRepository->update($id, $data);
            DB::commit();
            return $this->response200($category);
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function detail(Request $request, $id)
    {
        try {
            $category = $this->categoryRepository->find($id);
            if (!$category) {
                return $this->response404();
            }
            return $this->response200(new CategoryResource($category));

        } catch(\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $category = $this->categoryRepository->find($id);
            if (!$category) {
                return $this->response404();
            }
            $this->categoryRepository->update($id, ['is_active' => 0]);
            DB::commit();
            return $this->response200();
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }
}
