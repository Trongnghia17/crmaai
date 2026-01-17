<?php

namespace App\Repositories\ReceiptPayment;

use App\Models\Customer;
use App\Models\ReceiptPayment;
use App\Models\Supplier;
use App\Models\User;
use App\Repositories\BaseRepository;
use App\Repositories\ReceiptPayment\ReceiptPaymentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReceiptPaymentRepository extends BaseRepository implements ReceiptPaymentRepositoryInterface
{
    protected $model;

    public function __construct()
    {
        parent::__construct();
    }
    public function getModel()
    {
        return ReceiptPayment::class;
    }
    public function insert($data = [], $prefix = 'RE')
    {
        $row = $this->model->latest()->first();
        $number = 0;
        if ($row) {
            $number = $row->id + 1;
        }
        $this->getPartnerGroupName($data);
        $this->getPartnerName($data);
        $data['code'] = $this->getCD($number, $prefix);
        return $this->create($data);
    }
    public function updateById($data, $id)
    {
        $this->getPartnerGroupName($data);
        $this->getPartnerName($data);
        return $this->update($id, $data);
    }
    public function getPartnerGroupName(&$data): void
    {
        switch ($data['partner_group_id']) {
            case 2:
                $data['partner_group_name'] = ReceiptPayment::SUPPLIER;
                break;
            case 1:
                $data['partner_group_name'] = ReceiptPayment::CUSTOMER;
                break;
            case 3:
                $data['partner_group_name'] = ReceiptPayment::EMPLOYEE;
                break;
            case 4:
                $data['partner_group_name'] = ReceiptPayment::PARTNER_SHIP;
                break;
            case 5:
                $data['partner_group_name'] = ReceiptPayment::PARTNER_DIF;
                break;
        }
    }
    public function getPartnerName(&$data): void
    {
        switch ($data['partner_group_id']) {
            case 2:
                $data['partner_name'] = Supplier::query()->find($data['partner_id'])?->name;
                break;
            case 1:
                $data['partner_name'] = Customer::query()->find($data['partner_id'])?->name;
                break;
            case 3:
                $data['partner_name'] = User::query()->find($data['partner_id'])?->name;
                break;
        }
    }
    public static function getCD($id, $prefix): string
    {
        return $prefix . str_pad("{$id}", 5, '0', STR_PAD_LEFT);
    }
    public function receipt($request)
    {
        $query = $this->model
            ->with('receiptType')
            ->where('type', 1);

        build_query_by_user_id($query, auth()->user());

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        $size = $request->get('per_page', 20);
        return $query->orderByDesc('id')->with('order', 'user')->paginate($size);
    }
    public function payment($request)
    {
        $query = $this->model
            ->with('receiptType')
            ->where('type', 2);

        build_query_by_user_id($query, auth()->user());

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }
        $size = $request->get('per_page', 20);
        return $query->orderByDesc('id')->with('order', 'user')->paginate($size);
    }
    public function getAllRecords(Request $request = null, $type = null)
    {
        $query = $this->model
            ->with('receiptType')
            ->where('price', '!=', 0);

        build_query_by_user_id($query, auth()->user());

        if ($request->has('type')) {
            if ($request->type == 1) {
                $query->where(function ($query) use ($request) {
                    $query->where('type', $request->type)->where('price', '>=', 0)
                        ->orWhere(function ($query) {
                            $query->where('price', '>=', 0)->where('type', 2);
                        });
                });
            } else {
                $query->where(function ($query) use ($request) {
                    $query->where('type', $request->type)->where('price', '<', 0)
                        ->orWhere(function ($query) {
                            $query->where('price', '<', 0)->where('type', 1);
                        });
                });
            }
        }

        if ($request->has('is_auto')) {
            $isAuto = $request->boolean('is_auto');
            $query->where('is_edit', !$isAuto);
        }

        if ($request->has('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->has('search')) {
            $search = trim($request->search);
            $query->where(function ($query) use ($search) {
                $query->where('code', 'LIKE', "%$search%")
                    ->orWhere('partner_name', 'LIKE', "%$search%")
                    ->orWhere('note', 'LIKE', "%$search%");
            });
        }

        if ($request->start_date && $request->end_date) {
            $query->where('time', '>=', $request->start_date)
                ->where('time', '<=', $request->end_date);
        } else {
            $query->where('time', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'))
                ->where('time', '<=', Carbon::today()->format('Y-m-d'));
        }

        $size = $request->get('per_page', 20);
        if (!$type) {
            build_query_sort_field($query, $request->get('sort', 'id'));
            return $query->orderByDesc('id')->with('order', 'user')->paginate($size);
        }

        return $query->orderByDesc('id')->with('order', 'user')->get();
    }

    public static function getPaymentType($payment_type = 1): string
    {
        return match ($payment_type) {
            1 => 'cash',
            2 => 'bank',
            3 => 'credits',
            default => 'cod',
        };
    }

}
