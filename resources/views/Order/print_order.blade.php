<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<style>
    body {
        font-size: 18px;
        font-weight: bold !important;
    }

    .container {
        max-width: 426px;
    }

    .logo img {
        max-width: 35%;
        margin-bottom: 5px;
    }

    .address {
        font-size: 22px;
        margin-bottom: 10px;
        text-align: center;
    }

    .title {
        text-transform: uppercase;
        text-align: center;
        font-weight: 700;
        font-size: 22px;
        margin: 0px 0px;
    }

    .line-dashed {
        border-bottom: 1px dashed black;
        margin: 10px 0px;
    }

    .invoice_top p {
        font-size: 14px;
        margin: 0
    }

    .item_cart table {
        font-size: 14px;
        width: 100%;
    }

    .total {
        padding: 0px 5px
    }

    .total table {
        width: 100%;
        font-weight: 700;
    }

    .total table tr td:first-child {
        font-weight: 700;
    }

    .w-full {
        width: 100%;
        padding: 0px 5px;
    }

    .ql-align-center {
        text-align: center !important;
    }

    .ql-align-right {
        text-align: center !important;
    }
</style>

@php
    $total = 0;
    $user = auth()->user();
    $totalOrder = 0;
@endphp

<div class="container pt-3">
    <div class="line-dashed"></div>
    <p class="title">{{ $user->store_name }}</p>

    <div class="line-dashed"></div>

    <!-- Tiêu đề -->
    @if($order->type == 1)
        <p class="title">HÓA ĐƠN BÁN HÀNG</p>
    @elseif($order->type == 2)
        <p class="title">HÓA ĐƠN NHẬP HÀNG</p>
    @else
        <p class="title">HÓA ĐƠN TRẢ HÀNG</p>
    @endif

    <div class="line-dashed"></div>



    <!-- QR Code -->
    <div class="qr" style="text-align: center; margin-top: 20px; margin-bottom: 20px;">
        <div style="
        display: inline-block;
        padding: 10px;
        border: 2px dashed #ccc;
        border-radius: 8px;
    ">
            {!! $qrCode !!}
        </div>
    </div>

    <div class="row" style="padding: 0 2%; width: 100%; font-size: 14px !important;">
        <div class="col-sm-6 col-6" style="float:left; width: 50%">
            <div class="invoice_top">
                <p><strong>T.Ngân</strong>: {{$order->user->name ?? ''}}</p>
                <p><strong>SDT</strong>: {{$order->user->phone}}</p>
                <p><strong>No</strong>: {{ @$order->user->code}}</p>
            </div>
        </div>

        <div class="col-sm-6 col-6" style="float:left; width: 48%">
            <div class="invoice_top">
                <p><strong>Thời gian</strong>: {{ $order->created_date ?? $order->created_at }}</p>
                @if($order->type == 1)
                    <p><strong>Khách</strong>: {{$order->customer->name ?? ''}}</p>
                    <p><strong>SDT</strong>: {{ $order->customer->phone ?? '' }}</p>
                @elseif($order->type == 2)
                    <p><strong>Nhà cung cấp</strong>: {{$order->supplier->name ?? ''}}</p>
                    <p><strong>SDT</strong>: {{ $order->supplier->phone ?? '' }}</p>
                @endif

            </div>
        </div>
    </div>

    @if ($order->type == 1)
    <div class="item_cart" style="font-size: 14px !important;">
        <div class="col">
            <table class="table table-bordered">
                <tr>
                    <th style="width: 40%;">Mặt hàng</th>
                    <th>SL</th>
                    <th>Đơn giá</th>
                    <th class="text-end">T.Tiền</th>
                </tr>
                <!-- Dữ liệu sản phẩm sẽ được thêm ở đây -->
                @foreach($order->orderDetail ?? [] as $item)

                    @php
                    $total += $item->retail_cost;

                    @endphp
                    <tr>
                        <td style="width: 40%;">{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->product->retail_cost, 0, ',', '.') }} đ</td>
                        <td class="text-end">{{ number_format($item->product->retail_cost * $item->quantity, 0, ',', '.') }} đ</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <div class="line-dashed"></div>
    @elseif($order->type == 2)
        <div class="item_cart" style="font-size: 14px !important;">
            <div class="col">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 40%;">Mặt hàng</th>
                        <th>SL</th>
                        <th>Đơn giá</th>
                        <th class="text-end">T.Tiền</th>
                    </tr>
                    @foreach($order->orderDetail ?? [] as $item)

                        @php
                            $total += $item->base_cost;
                        @endphp

                        <tr>
                            <td style="width: 40%;">{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->product->entry_cost, 0, ',', '.') }} đ</td>
                            <td class="text-end">{{ number_format($item->product->entry_cost * $item->quantity, 0, ',', '.') }} đ</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>

        <div class="line-dashed"></div>
    @endif

    @if ($order->type == 1)
    <!-- Tổng cộng -->
    <div class="total" style="font-size: 14px !important;">
        <table>
            <tr>
                <td>TỔNG GIÁ TRỊ HÓA ĐƠN</td>
                <td style="text-align: right">{{  number_format($total, 0, ',', '.') }} đ</td>
            </tr>
            <tr>
                <td>GIẢM GIÁ</td>
                @if ($order->discount_type == 2)
                    <td style="text-align: right">{{ number_format($order->discount) . ' đ'}} </td>
                    @php
                        $totalOrder = $total - $order->discount;
                    @endphp
                @else
                    <td style="text-align: right">{{ number_format($order->discount) . '%'}}</td>
                    @php
                        $totalOrder = $total - ($total * $order->discount / 100);
                    @endphp
                @endif
            </tr>
            <tr>
                <td>VAT</td>

                @if ($order->vat > 0)
                    <td style="text-align: right">{{ $order->vat . '%' }}</td>
                    @php
                        $totalOrder = $totalOrder + ($totalOrder * $order->vat / 100);
                    @endphp
                @else
                    <td style="text-align: right">0%</td>
                @endif
            </tr>

            <tr>
                <td>TỔNG TIỀN</td>

                <td style="text-align: right">{{ number_format($order->retail_cost, 0, ',', '.')}} đ</td>
            </tr>

            @php
                $orderPayment = collect($order->orderPayment)->sum('price');

            @endphp
            <tr>
                <td>ĐÃ THANH TOÁN</td>
                <td style="text-align: right">{{ number_format($orderPayment, 0, ',', '.')}} đ</td>
            </tr>

            @if ($order->retail_cost - $orderPayment > 0 )
                <tr>
                    <td>CÒN NỢ</td>
                    <td style="text-align: right">{{ number_format(($order->retail_cost - $orderPayment) ,0, ',', '.')}} đ</td>
                </tr>
                @if ($customer_debt && $customer_debt > 0)
                    <tr>
                        <td>NỢ CŨ</td>
                        <td style="text-align: right">{{ number_format(($customer_debt - ($order->retail_cost - $orderPayment)), 0, ',', '.' )}} đ</td>
                    </tr>
                    <tr>
                        <td>TỔNG NỢ</td>
                        <td style="text-align: right">{{ number_format($customer_debt, 0, ',', '.' )}} đ</td>
                    </tr>
                @endif
            @else
                <tr>
                    <td>TIỀN THỪA TRẢ KHÁCH</td>
                    <td style="text-align: right">{{ number_format(($orderPayment - $order->retail_cost), 0, ',', '.' )}} đ</td>
                </tr>
            @endif


        </table>
    </div>

    @elseif($order->type == 2)
        <div class="total" style="font-size: 14px !important;">
            <table>
                <tr>
                    <td>TỔNG GIÁ TRỊ HÓA ĐƠN</td>
                    <td style="text-align: right">{{  number_format($total, 0, ',', '.') }} đ</td>
                </tr>
                <tr>
                    <td>GIẢM GIÁ</td>
                    @if ($order->discount_type == 2)
                        <td style="text-align: right">{{ number_format($order->discount, 0, ',', '.') }} đ</td>
                        @php
                            $totalOrder = $total - $order->discount;
                        @endphp
                    @else
                        <td style="text-align: right">{{ number_format($order->discount) . '%'}}</td>
                        @php
                            $totalOrder = $total - ($total * $order->discount / 100);
                        @endphp
                    @endif
                </tr>
                <tr>
                    <td>VAT</td>

                    @if ($order->vat > 0)
                        <td style="text-align: right">{{ $order->vat . '%' }}</td>
                        @php
                            $totalOrder = $totalOrder + ($totalOrder * $order->vat / 100);
                        @endphp
                    @else
                        <td style="text-align: right">0%</td>
                    @endif
                </tr>

                <tr>
                    <td>TỔNG TIỀN</td>
                    <td style="text-align: right">{{ number_format($order->base_cost, 0, ',', '.') }} đ</td>
                </tr>

                @php
                    $orderPayment = collect($order->orderPayment)->sum('price');

                @endphp
                <tr>
                    <td>ĐÃ THANH TOÁN</td>
                    <td style="text-align: right">{{ number_format($orderPayment, 0, ',', '.') }} đ</td>
                </tr>

                @if ($totalOrder - $orderPayment > 0 )
                    <tr>
                        <td>CÒN NỢ</td>
                        <td style="text-align: right">{{ number_format(($order->base_cost - $orderPayment), 0, ',', '.') }} đ</td>
                    </tr>


                    @if ($customer_debt && $customer_debt->remaining_debt > 0)
                        <tr>
                            <td>TỔNG NỢ</td>
                            <td style="text-align: right">{{ number_format(($customer_debt->remaining_debt), 0, ',', '.')}} đ</td>
                        </tr>
                    @endif
                @else
                    <tr>
                        <td>TIỀN THỪA TRẢ KHÁCH</td>
                        <td style="text-align: right">{{ number_format(($orderPayment - $order->base_cost), 0, ',', '.' )}} đ</td>
                    </tr>
                @endif

            </table>
        </div>
    @endif
    <div class="line-dashed"></div>
    <table class="w-full" style=" font-size: 14px !important;">
        <tr>
            <td class="fs-6">Số tham chiếu</td>
            <td class="fs-6 text-end" style="text-align: right">{{@$order->code}}</td>
        </tr>
    </table>
    <div class="line-dashed"></div>
    <p class="text-center fs-6 mb-1" style="text-align: center;font-size: 14px !important;">Chỉ xuất hóa đơn trong ngày</p>
    <p class="text-center fs-6" style="text-align: center; font-size: 14px !important;">Tax Invoice will be issued within same day</p>
    <div class="line-dashed"></div>

    <p class="text-center fs-5" style="text-align: center; font-size: 14px !important;">CẢM ƠN QUÝ KHÁCH VÀ HẸN GẶP LẠI</p>
</div>

</body>
</html>
