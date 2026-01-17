<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ReceiptPayment\DetailRequest;
use App\Http\Requests\ReceiptPayment\StorePaymentRequest;
use App\Http\Requests\ReceiptPayment\StoreRequest;
use App\Http\Requests\ReceiptPayment\UpdatePaymentRequest;
use App\Http\Requests\ReceiptPayment\UpdateRequest;
use App\Http\Resources\ReceiptPayment\ReceiptPaymentResource;
use App\Jobs\ReceiptPayment\CreatePayment;
use App\Jobs\ReceiptPayment\CreateReceipt;
use App\Jobs\ReceiptPayment\DeletePayment;
use App\Jobs\ReceiptPayment\DeleteReceipt;
use App\Repositories\Pagination;
use App\Repositories\ReceiptPayment\ReceiptPaymentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceiptPaymentController extends Controller
{
    protected $receiptPaymentRepository;
    public function __construct(
        ReceiptPaymentRepositoryInterface $receiptPaymentRepository
    )
    {
        $this->receiptPaymentRepository = $receiptPaymentRepository;
    }

    public function createReceipt(StoreRequest $request)
    {
        Log::info(json_encode($request->all()));
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $result = $this->receiptPaymentRepository->insert($data);

            $now = date('Y-m-d');
            $time = $request->get('time', date('Y-m-d'));

            if ($now > $time) {
                dispatch(new CreateReceipt($result, auth()->user()));
            }
            DB::commit();
            return $this->response200($result);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception);
            return $this->response500($exception->getMessage());
        }
    }

    public function updateReceipt(UpdateRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $result = $this->receiptPaymentRepository->update($data, $id);
            DB::commit();

            return $this->response200($result);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception);
            return $this->response500($exception->getMessage());
        }
    }

    public function receipt(Request $request)
    {
        try {
            $result = $this->receiptPaymentRepository->receipt($request);
            $paginate = new Pagination($result);
            return $this->response200(
                ReceiptPaymentResource::collection($paginate->getItems()),
                $paginate->getMeta()
            );
        } catch (\Exception $exception) {
            Log::info($exception);
            return $this->response500($exception->getMessage());
        }
    }

    public function detail(DetailRequest $request, $id)
    {
        try {
            $result = $this->receiptPaymentRepository->find($id);
            return $this->response200(
                new ReceiptPaymentResource($result)
            );
        } catch (\Exception $exception) {
            Log::info($exception);
            return $this->response500($exception->getMessage());
        }
    }

    public function createPayment(StorePaymentRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $data['type'] = 2;
            $data['price'] = '-' . $data['price'];
            $result = $this->receiptPaymentRepository->insert($data, 'PE');

            $now = date('Y-m-d');
            $time = $request->get('time', date('Y-m-d'));

            if ($now > $time) {
                dispatch(new CreatePayment($result, auth()->user()));
            }

            DB::commit();
            return $this->response200($result);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception);
            return $this->response500($exception->getMessage());
        }
    }

    public function updatePayment(UpdatePaymentRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $data['price'] = '-' . $data['price'];
            $result = $this->receiptPaymentRepository->update($data, $id);

            $now = date('Y-m-d');
            $time = $request->get('time', date('Y-m-d'));

            if ($now > $time) {
                dispatch(new CreatePayment($result, auth()->user()));
            }

            DB::commit();
            return $this->response200($result);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::info($exception);
            return $this->response500($exception->getMessage());
        }
    }

    public function payment(Request $request)
    {
        try {
            $result = $this->receiptPaymentRepository->payment($request);
            $paginate = new Pagination($result);

            return $this->response200(
                ReceiptPaymentResource::collection($paginate->getItems()),
                $paginate->getMeta()
            );
        } catch (\Exception $exception) {
            Log::info($exception);
            return $this->response500($exception->getMessage());
        }
    }
    public function delete(DetailRequest $request, $id)
    {
        try {
            $result = $this->receiptPaymentRepository->find($id);
            $result->status = 0;
            $result->save();

            $now = date('Y-m-d');
            $time = $result->time;

            if ($now > $time) {
                if ($result->type == 2) {
                    dispatch(new DeletePayment($result, auth()->user()));
                } else {
                    dispatch(new DeleteReceipt($result, auth()->user()));
                }
            }
            return $this->response200();
        } catch (\Exception $exception) {
            Log::info($exception);
            return $this->response500($exception);
        }
    }

}
