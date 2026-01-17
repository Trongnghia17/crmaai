<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>BẢNG CHI TIẾT CÔNG NỢ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            margin-top: 20px;
        }
        .date-range {
            text-align: center;
        }
        .subheader {
            text-align: center;
            font-style: italic;
        }
        .info {
            margin-top: 20px;
        }
        .right-align {
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    @php
        $total_price = 0;
        $total_payment = 0;
    @endphp
    <div class="info">
        <strong>CỬA HÀNG: {{$user->store_name}}</strong><br>
        Địa chỉ: {{$user->address}}<br>
        SĐT: {{ $user->phone }}<br>
        Ngày lập: {{ \Carbon\Carbon::parse()->format('d/m/Y') }}<br>
    </div>
    <table>
        <thead>
        <tr>
            @if ($type == 1)
                <th colspan="12" style="text-align: center; font-size: 18px;">BẢNG DANH SÁCH ĐƠN HÀNG</th>
            @elseif($type ==2)
                <th colspan="12" style="text-align: center; font-size: 18px;">BẢNG DANH SÁCH ĐƠN NHẬP HÀNG</th>
            @endif
        </tr>
        <tr>
            <th colspan="12" style="text-align: center;">
                Từ ngày: {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} - đến ngày: {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
            </th>
        </tr>
        <tr>
            <th style="background-color: #ffff99;">Ngày tạo đơn</th>
            <th style="background-color: #ffff99;">STT</th>
            <th style="background-color: #ffff99;">Mã</th>
            @if($type == 1)
                <th style="background-color: #ffff99;">Tên khách hàng</th>
            @elseif($type == 2)
                <th style="background-color: #ffff99;">Tên nhà cung cấp</th>
            @endif
            <th style="background-color: #ffff99;">Tên sản phẩm</th>
            <th style="background-color: #ffff99;">Số lượng</th>
            <th style="background-color: #ffff99;">Đơn vị</th>
            <th style="background-color: #ffff99;">Đơn giá</th>
            <th style="background-color: #ffff99;">Thành tiền</th>
            <th style="background-color: #ffff99;">Tổng tiền TT</th>
            <th style="background-color: #ffff99;">Đã thanh toán</th>
            <th style="background-color: #ffff99;">Còn nợ</th>
        </tr>
        </thead>
        <tbody>
        @foreach($orders as $index => $order)
            @php
                $payment_temp = $order->orderPayment->sum('price') ?? 0;
                $details = $order->orderDetail ?? [];
                $rowspan = count($details);
                $total_order_price = 0;
                if ($type == 1)
                {
                       $total_order_price = $order->retail_cost ?? 0;
                } else {
                       $total_order_price = $order->base_cost ?? 0;
                }
                if ($total_order_price > $payment_temp)
                {
                     $debt = $total_order_price - $payment_temp;
                } else {
                    $debt = 0;
                }
                $payment = min($payment_temp, $total_order_price);
                $total_price += $total_order_price;
                $total_payment += $payment;
            @endphp

            @foreach($details as $i => $item)
                <tr>
                    @if($i == 0)
                        <td rowspan="{{ $rowspan }}">{{ $index + 1 }}</td>
                        <td rowspan="{{ $rowspan }}">{{ \Carbon\Carbon::parse($order->create_date)->format('d/m/Y') }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $order->code }}</td>
                        @if($type == 1)
                            <td rowspan="{{ $rowspan }}">{{ $order->customer->name ?? '' }}</td>
                        @elseif($type == 2)
                            <td rowspan="{{ $rowspan }}">{{ $order->supplier->name ?? '' }}</td>
                        @endif

                    @endif

                    <td>{{ $item->product->name ?? '' }}</td>
                    <td>{{ $item->quantity ?? 0 }}</td>
                    <td>
                        @php
                            $unit = $item->product->unit ?? '';

                        @endphp
                        {{ $unit }}
                    </td>
                        @if($type == 1)
                            <td>{{ number_format($item->product->retail_cost ?? 0, 0, ',', '.') }} VNĐ</td>
                            <td>{{ number_format($item->retail_cost ?? 0, 0, ',', '.') }} VNĐ</td>
                        @elseif($type == 2)
                            <td>{{ number_format($item->product->base_cost ?? 0, 0, ',', '.') }} VNĐ</td>
                            <td>{{ number_format($item->base_cost ?? 0, 0, ',', '.') }} VNĐ</td>
                        @endif

                    @if($i == 0)

                        <td rowspan="{{ $rowspan }}">{{ number_format($total_order_price, 0, ',', '.') }} VNĐ</td>
                        <td rowspan="{{ $rowspan }}">{{ number_format($payment, 0, ',', '.') }} VNĐ</td>
                        <td rowspan="{{ $rowspan }}">{{ number_format($debt, 0, ',', '.') }} VNĐ</td>
                    @endif
                </tr>
            @endforeach
        @endforeach

        </tbody>
    </table>

    <div class="right-align">
        <p>Phát sinh trong kỳ: {{ number_format($total_price, 0, ',', '.')}} VNĐ</p>
        <p>Đã thanh toán trong kỳ: {{ number_format($total_payment, 0, ',', '.')}} VNĐ</p>
        <p><strong>Nợ cuối kỳ (còn lại): {{ number_format($total_price - $total_payment, 0, ',', '.')}} VNĐ</strong></p>
    </div>
</div>
</body>
</html>
