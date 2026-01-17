<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ReceiptType\ReceiptTypeResource;
use App\Repositories\Pagination;
use App\Repositories\ReceiptTypeRepository;
use Illuminate\Http\Request;

class ReceiptTypeController extends Controller
{
    protected $receiptType;
    public function __construct(
        ReceiptTypeRepository $receiptTypeRepository
    )
    {
        $this->receiptType = $receiptTypeRepository;
    }

    public function index(Request $request)
    {
        try {
            $result = $this->receiptType->getAllType($request);
            $paginate = new Pagination($result);

            return $this->response200(
                ReceiptTypeResource::collection($paginate->getItems()),
                $paginate->getMeta()
            );
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function create(Request $request)
    {
        try {
            $data = $this->receiptType->getData($request);
            $result = $this->receiptType->create($data);

            return $this->response200($result);
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $data = $this->receiptType->getData($request);
            $this->receiptType->update($id, $data);

            return $this->response200();
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }


    public function detail($id)
    {
        try {
            $result = $this->receiptType->find($id);

            return $this->response200($result);
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            if(!$this->receiptType->find($id)) {
                return $this->response400('ID không hợp lệ');
            }
            $result = $this->receiptType->update($id,['status' => false]);
            return $this->response200($result);
        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }
}
