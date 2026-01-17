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

    <!-- Logo -->
    {{--    <div class="logo" style="text-align: center;">--}}
    {{--        <img src="" alt="Logo" class="img-fluid">--}}
    {{--    </div>--}}

    <div class="line-dashed"></div>

    <!-- Tiêu đề -->
    <p class="title">HÓA ĐƠN BÁN HÀNG</p>
    <div class="line-dashed"></div>

    <!-- QR Code -->
    {{--    <div class="qr" style="text-align: center;">--}}
    {{--        <img src="" alt="QR Code" style="width: 40%;">--}}
    {{--    </div>--}}

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
                <p><strong>Khách</strong>: {{$order->customer->name ?? ''}}</p>
                <p><strong>SDT</strong>: {{ $order->customer->phone ?? '' }}</p>
            </div>
        </div>
    </div>

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


    <!-- Tổng cộng -->
    <div class="total" style="font-size: 14px !important;">
        <table>
            <tr>
                <td>TỔNG GIÁ TRỊ HÓA ĐƠN</td>
                <td style="text-align: right">{{ $total }} đ</td>
            </tr>
            <tr>
                <td>GIẢM GIÁ</td>
                @if ($order->discount_type == 2)
                    <td style="text-align: right">{{ number_format($order->discount) . 'đ'}} </td>
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
                <td style="text-align: right">{{ $totalOrder . ' đ' }}</td>
            </tr>
        </table>
    </div>
</div>


</body>
</html>
