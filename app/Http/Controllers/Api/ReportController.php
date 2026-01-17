<?php

namespace App\Http\Controllers\Api;


use App\Http\Resources\Report\CustomerResource;
use App\Http\Resources\PaginationResource;
use App\Http\Resources\Report\ProductStorageResource;
use App\Http\Resources\Report\SupplierResource;
use App\Models\Aggregate;
use App\Models\Order;
use App\Models\Supplier;
use App\Repositories\Customer\CustomerRepository;
use App\Repositories\Customer\CustomerRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\OrderDetail\OrderDetailRepositoryInterface;
use App\Repositories\ProductStorage\ProductStorageRepository;
use App\Repositories\ProductStorage\ProductStorageRepositoryInterface;
use App\Repositories\ProfitLoss\ProfitLossRepositoryInterface;
use App\Repositories\ReceiptPayment\ReceiptPaymentRepositoryInterface;
use App\Repositories\Report\ReportRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Pagination;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    protected $reportRepository;

    protected $orderRepository;

    protected $orderDetailRepository;

    protected $customerRepository;

    protected $productStorageRepository;

    protected $profitlossRepo;

    protected $receiptPaymentRepository;

    public function __construct(
        ReportRepositoryInterface         $reportRepository,
        OrderRepositoryInterface          $orderRepository,
        OrderDetailRepositoryInterface    $orderDetailRepository,
        CustomerRepositoryInterface       $customerRepository,
        ProductStorageRepositoryInterface $productStorageRepository,
        ProfitLossRepositoryInterface     $profitlossRepository,
        ReceiptPaymentRepositoryInterface $receiptPaymentRepository,
    )
    {
        $this->reportRepository = $reportRepository;
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->orderDetailRepository = $orderDetailRepository;
        $this->productStorageRepository = $productStorageRepository;
        $this->profitlossRepo = $profitlossRepository;
        $this->receiptPaymentRepository = $receiptPaymentRepository;
    }

    public function aggregate(Request $request)
    {
        try {
            $report = $this->orderDetailRepository->aggregate($request);
            $data = $this->reportRepository->aggregate($report);

            $total = [
                'base_cost' => collect($data)->sum('base_cost'),
                'wholesale_cost' => collect($data)->sum('wholesale_cost'),
                'retail_cost' => collect($data)->sum('retail_cost'),
                'retail_cost_before_vat' => collect($data)->sum('retail_cost_before_vat'),
                'base_cost_before_vat' => collect($data)->sum('base_cost_before_vat'),
                'retail_cost_before_discount' => collect($data)->sum('retail_cost_before_discount'),
                'base_cost_before_discount' => collect($data)->sum('base_cost_before_discount'),
                'entry_cost' => collect($data)->sum('entry_cost'),
                'vat_cost' => collect($data)->sum('vat_cost'),
                'quantity' => collect($data)->sum('quantity'),
                'sub_unit_quantity' => collect($data)->sum('sub_unit_quantity'),
            ];

            $page = $request->get('page');
            $perPage = $request->get('per_page', 10);
            $result = new Pagination(
                collect($data)->forPage($page, $perPage),
                count($data),
                $perPage,
                $page
            );

            return $this->response200(
                array_values($result->items()),
                new PaginationResource($result),
                null,
                $total
            );

        } catch (\Exception $e) {
            return $this->response500($e->getMessage());
        }
    }

    public function storage(Request $request)
    {
        try {

            Log::info('storage' . json_encode($request->all()));
            $productStorage = $this->productStorageRepository->getStorage($request);
            $data = $productStorage->sortByDesc('')->each(function (&$item) use ($request) {
                if ($item->total_khoi_tao_kho > 0) {
                    $item['start'] = $item->total_khoi_tao_kho;
                } else {
                    $start = $this->productStorageRepository->getStorageStart($request, $item->product_id);
                    $item['start'] = ($start) ? $start->quantity : 0;
                }

                $end = $this->productStorageRepository->getStorageEnd($request, $item->product_id);

                $item['end'] = ($end) ? $end->quantity : 0;
                $item['price'] = (@$item->product->entry_cost) ? $item['end'] * ($item->product->entry_cost) : 0;
            });
            $data = $data->sortByDesc("product_id")->values()->all();
            $page = $request->get('page');
            $perPage = $request->get('per_page', 20);
            $result = new Pagination(
                collect($data)->forPage($page, $perPage),
                count($data),
                $perPage,
                $page
            );

            return $this->response200(
                ProductStorageResource::collection($result->items()),
                new PaginationResource($result),
                null,
                [
                    'total_inventory' => collect($data)->sum('end'),
                    'total_price' => collect($data)->sum('price')
                ]
            );

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->response500($e->getMessage());

        }
    }

    public function debt(Request $request)
    {
        try {
            $data = $this->orderRepository->debt($request);
            $paginate = new \App\Repositories\Pagination($data);

            $totalRecord = $this->orderRepository->debt($request, 'all');

            return $this->response200(
                $paginate->getItems(),
                $paginate->getMeta(),
                null,
                $this->_totalDebt($totalRecord, $request->get('type', 1))
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());;
            return $this->response500($e->getMessage());
        }
    }

    private function _totalDebt($data, $type)
    {
        $cost = collect($data)->sum('retail_cost');
        $totalPayment = collect($data)->sum(fn($order) => $order->orderPayment->sum('price'));
        if ($type == 1) {
            $dept = $cost - $totalPayment;
        } else {
            $dept = $cost - $totalPayment;
        }
        return [
            'cost' => $cost,
            'paid' => $totalPayment,
            'dept' => $dept,
        ];
    }

    public function customer(Request $request)
    {

        Log::info('customer' . json_encode($request->all()));
        $customers = $this->orderRepository->customer_report($request);

        $customerNull = $this->orderRepository->revenueByCustomerNull($request);
        $customerOrderNull = $this->customerRepository->revenueByOrderNull($request);

        $data = $customers;
        Log::info('customer' . json_encode($customers));;
        Log::info('customer_order' . json_encode($customerOrderNull));
        Log::info('customer_null' . json_encode($customerNull));;

        foreach ($customerOrderNull->toArray() ?? [] as $key => $val) {
            if ($val['pay_debt_old'] >= $val['debt_old'])
                continue;
            $data[] = [
                'customer' => [
                    'name' => @$val['name'] ?? null,
                    'phone' => @$val['phone'] ?? null,
                    'id' => @$val['id'] ?? null,
                    'debt_old' => $val['debt_old'] ?? 0,
                    'pay_debt_old' => $val['pay_debt_old'] ?? 0,
                ],
                'total_pay' => $val['pay_debt_old'] ?? 0,
                'number' => $val['number'] ?? 0,
                'total' => $val['debt_old'] ?? 0,
            ];
        }

        array_push($data, $customerNull);
        $data = collect($data)->sortByDesc('customer.id')->toArray();
//        $data = collect($data)->sortByDesc(function ($item) {
//            return $item['customer']['id'] ?? 0;
//        })->toArray();
        $page = $request->get('page');
        $perPage = $request->get('per_page', 20);
        $result = new Pagination(
            collect($data)->forPage($page, $perPage),
            count($data),
            $perPage,
            $page
        );

        return $this->response200(
            CustomerResource::collection($result->items()),
            new PaginationResource($result),
            null,
            [
                'number' => collect($data)->sum('number'),
                'total' => collect($data)->sum('total'),
                'total_pay' => collect($data)->sum('total_pay')
            ]
        );
    }

    public function supplier(Request $request)
    {
        $orders = $this->orderRepository->supplier($request);
        $data = [];
        $i = 0;
        $type = $request->get('type', 1);
        foreach ($orders->groupBy('supplier_id')->toArray() ?? [] as $key => $item) {
            $pay = collect($item)->sum('total_pay');
            $orderId = collect($item)->pluck('id')->toArray();
            $orders = Order::query()->whereIn('id', $orderId)->get();
            $total = $orders->sum('base_cost');
            if ($type == 2 && $pay >= $total) {
                continue;
            }

            if (!$key) {
                $data[$i]['supplier'] = [
                    'name' => 'Đại lý',
                    'id' => -1
                ];
            } else {
                $data[$i]['supplier'] = Supplier::query()->find($key);
            }

            $data[$i]['total_pay'] = ($pay) ? $pay : 0;
            $data[$i]['number'] = count($item);
            $data[$i]['total'] = $total;

            if (
                $data[$i]['total'] == 0
                && $data[$i]['number'] == 0
                && $data[$i]['total_pay'] == 0
            ) {
                unset($data[$i]);
            }

            $i++;
        }

        $page = $request->get('page');
        $perPage = $request->get('per_page', 20);
        $result = new Pagination(
            collect($data)->forPage($page, $perPage),
            count($data),
            $perPage,
            $page
        );

        return $this->response200(
            SupplierResource::collection($result->items()),
            new PaginationResource($result),
            null,
            [
                'number' => collect($data)->sum('number'),
                'total' => collect($data)->sum('total'),
                'total_pay' => collect($data)->sum('total_pay')
            ]
        );
    }

    public function profitAndLoss(Request $request)
    {
        Log::info(json_encode($request->all()));
        $data = $result = $this->profitlossRepo->getReport($request);
        if ($request->start_date && $request->end_date
            && $request->end_date >= date('Y-m-d')
        ) {
            $resultToday = $this->profitlossRepo->getReportToday();
            $data = collect([
                $result,
                $resultToday
            ]);
            $data = [
                "revenue_sale" => collect($data)->sum('revenue_sale') ?? 0,
                "discount_sale" => collect($data)->sum('discount_sale') ?? 0,
                "order_cancel" => collect($data)->sum('order_cancel') ?? 0,
                "cost_sale" => collect($data)->sum('cost_sale') ?? 0,
                "vat" => collect($data)->sum('vat') ?? 0,
                "other_income" => collect($data)->sum('other_income') ?? 0,
                "other_expense" => collect($data)->sum('other_expense') ?? 0,


            ];
        }
        return $this->response200($data);
    }

    public function receiptPayment(Request $request)
    {
        Log::info(json_encode($request->all()));
        $data = $this->receiptPaymentRepository->getAllRecords($request);
        $paginate = new \App\Repositories\Pagination($data);

        $totalRecord = $this->receiptPaymentRepository->getAllRecords($request, 'all');

        return $this->response200(
            $paginate->getItems(),
            $paginate->getMeta(),
            null,
            $this->_totalReceiptPayment($request, $totalRecord)
        );
    }

    private function _totalReceiptPayment(Request $request, $totalRecord)
    {
        $total = $total_receipt = $total_payment = 0;
        $query = Aggregate::query()->where('user_id', auth()->id());

        if ($request->start_date && $request->end_date) {
            $query->where('time', '<', $request->start_date);
        } else {
            $query->where('time', '<', Carbon::now()->startOfMonth()->format('Y-m-d'));
        }

        $lastRecord = $query->latest('time')->first();
        $total += ($lastRecord) ? $lastRecord->total : 0;
        $total_receipt = $total_payment = 0;
        $total_receipt += collect($totalRecord)->where('price', '>', 0)->sum('price');

        $total_payment += collect($totalRecord)->where('price', '<', 0)->sum('price');


        return [
            'total' => $total,
            'total_receipt' => $total_receipt,
            'total_payment' => $total_payment
        ];
    }
}
