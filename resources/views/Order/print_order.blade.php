<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<style>
    @page {
        size: 80mm auto;
        margin: 0;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 0;
        font-size: 11px;
        font-family: Arial, sans-serif;
        width: 80mm;
    }

    .container {
        width: 80mm;
        max-width: 80mm;
        margin: 0;
        padding: 5px 10px;
    }

    .store-info {
        text-align: center;
        font-size: 10px;
        line-height: 1.4;
        margin-bottom: 8px;
    }

    .store-name {
        font-weight: bold;
        font-size: 12px;
    }

    .title {
        text-transform: uppercase;
        text-align: center;
        font-weight: bold;
        font-size: 13px;
        margin: 0;
    }

    .line-dashed {
        border-bottom: 1px dashed #000;
        margin: 8px 0;
    }

    .invoice_top {
        font-size: 10px;
        line-height: 1.5;
        margin-bottom: 8px;
    }

    .invoice_top p {
        margin: 2px 0;
    }

    .item_cart table {
        font-size: 10px;
        width: 100%;
        border-collapse: collapse;
    }

    .item_cart th {
        font-weight: bold;
        text-align: left;
        padding: 4px 2px;
        border-bottom: 1px dashed #000;
    }

    .item_cart td {
        padding: 4px 2px;
        vertical-align: top;
    }

    .item_cart .text-right {
        text-align: right;
    }

    .item_cart .text-center {
        text-align: center;
    }

    .total {
        font-size: 11px;
        margin-top: 8px;
    }

    .total table {
        width: 100%;
    }

    .total td {
        padding: 3px 0;
    }

    .total .label {
        font-weight: normal;
    }

    .total .value {
        text-align: right;
        font-weight: bold;
    }

    .qr-code {
        text-align: center;
        margin: 15px 0;
    }

    .footer-text {
        text-align: center;
        font-size: 10px;
        line-height: 1.4;
        margin: 5px 0;
    }

    .reference {
        font-size: 10px;
        margin: 5px 0;
    }
</style>

@php
    $total = 0;
    $totalDiscount = 0; // Tổng chiết khấu từng sản phẩm
    $user = auth()->user();
    $totalOrder = 0;
@endphp

<div class="container">
    <div class="store-info">
        <div class="store-name">AAIPHARMA</div>
            <div>Địa chỉ: SH2B, HH03 Eco Lake View, 32 Đại Từ, Định Công, Hà Nội</div>
        
    </div>

    <div class="line-dashed"></div>

    <!-- Tiêu đề -->
    @if($order->type == 1)
        <p class="title">HÓA ĐƠN TẠM TÍNH</p>
    @elseif($order->type == 2)
        <p class="title">HÓA ĐƠN NHẬP HÀNG</p>
    @else
        <p class="title">HÓA ĐƠN TRẢ HÀNG</p>
    @endif
    
    <div style="text-align: center; font-size: 10px; margin-bottom: 8px;">
        Ngày {{ date('d', strtotime($order->created_at)) }} tháng {{ date('m', strtotime($order->created_at)) }} năm {{ date('Y', strtotime($order->created_at)) }}
    </div>

    <div class="invoice_top">
        @if($order->type == 1)
            <p>Khách hàng: {{$order->customer->name ?? 'Khách lẻ'}}</p>
        @elseif($order->type == 2)
            <p>Nhà cung cấp: {{$order->supplier->name ?? ''}}</p>
        @endif
        <p>Số hóa đơn: {{@$order->code}}</p>
        <p>Nhân viên: {{$order->user->name ?? ''}}</p>
    </div>

    <div class="line-dashed"></div>

    @if ($order->type == 1)
    <div class="item_cart">
        <table>
            <thead>
                <tr>
                    <th>Tên</th>
                    <th class="text-center">SL</th>
                    <th class="text-right">Đơn giá</th>
                    <th class="text-right">Giảm giá</th>
                    <th class="text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderDetail ?? [] as $item)
                    @php
                    $itemPrice = $item->product->retail_cost;
                    $itemSubTotal = $itemPrice * $item->quantity;
                    $discountAmount = 0;
                    
                    // Tính giảm giá nếu có
                    if ($item->discount > 0) {
                        if ($item->discount_type == 1) {
                            // Giảm theo %
                            $discountAmount = $itemSubTotal * $item->discount / 100;
                        } else {
                            // Giảm theo số tiền (giảm giá cho mỗi sản phẩm)
                            $discountAmount = $item->discount * $item->quantity;
                        }
                    }
                    
                    $itemTotal = $itemSubTotal - $discountAmount;
                    $total += $itemSubTotal; // Tổng tiền chưa trừ giảm giá
                    $totalDiscount += $discountAmount; // Tổng tiền giảm từng sản phẩm
                    @endphp
                    <tr>
                        <td>
                            {{ $item->product->name }}
                            @if($item->discount > 0)
                                <br><small style="color: black; font-size: 9px;">
                                    (Giảm: {{ $item->discount_type == 1 ? $item->discount.'%' : number_format($item->discount, 0, ',', '.') }})
                                </small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($itemPrice, 0, ',', '.') }}</td>
                        <td class="text-right">
                            @if($item->discount > 0)
                                {{ number_format($discountAmount, 0, ',', '.') }}
                            @else
                                0
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($itemTotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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
    <div class="total">
        <table>
            <tr>
                <td class="label">Tổng tiền hàng:</td>
                <td class="value">{{ number_format($total, 0, ',', '.') }}</td>
            </tr>
            @php
                // Tính tổng sau khi trừ chiết khấu sản phẩm
                $totalAfterProductDiscount = $total - $totalDiscount;
                
                // Tính chiết khấu đơn hàng (tính trên tổng sau khi trừ CK sản phẩm)
                $orderDiscount = 0;
                if ($order->discount > 0) {
                    if ($order->discount_type == 2) {
                        $orderDiscount = $order->discount;
                    } else {
                        $orderDiscount = $totalAfterProductDiscount * $order->discount / 100;
                    }
                }
                // Tổng chiết khấu = chiết khấu sản phẩm + chiết khấu đơn hàng
                $totalAllDiscount = $totalDiscount + $orderDiscount;
            @endphp
            @if ($totalDiscount > 0)
                <tr>
                    <td class="label">CK sản phẩm:</td>
                    <td class="value">-{{ number_format($totalDiscount, 0, ',', '.') }}</td>
                </tr>
            @endif
             @if ($orderDiscount > 0)
                <tr>
                    <td class="label">
                        Giảm giá
                    
                    </td>
                    <td class="value">{{  $order->discount_type == 1 ? $order->discount.'%' : number_format($order->discount, 0, ',', '.').'đ' }}</td>
                </tr>
            @endif
            @if ($orderDiscount > 0)
                <tr>
                    <td class="label">
                        Giảm giá đơn hàng:
                        
                    </td>
                    <td class="value">-{{ number_format($orderDiscount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @php
                $totalOrder = $total - $totalAllDiscount;
            @endphp

            @if ($order->vat > 0)
                <tr>
                    <td class="label">VAT ({{ $order->vat }}%):</td>
                    <td class="value">{{ number_format($totalOrder * $order->vat / 100, 0, ',', '.') }}</td>
                </tr>
                @php
                    $totalOrder = $totalOrder + ($totalOrder * $order->vat / 100);
                @endphp
            @endif
        </table>
    </div>

    <div class="line-dashed"></div>

    <div class="total">
        <table>
            <tr>
                <td class="label" style="font-size: 12px;"><strong>Tổng thanh toán:</strong></td>
                <td class="value" style="font-size: 12px;"><strong>{{ number_format($order->retail_cost, 0, ',', '.') }}</strong></td>
            </tr>
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

    <div class="footer-text" style="font-style: italic; color: black;">Aaipharma luôn đồng hành cùng quý khách hàng mọi sự hỗ trợ về về sản phẩm và sức khỏe xin vui lòng liên hệ với chúng tôi !</div>

    <!-- QR Code -->
    <div class="qr-code">
        {!! $qrCode !!}
    </div>

    <div class="footer-text" style="font-weight: bold;">Cảm ơn và hẹn gặp lại!</div>
</div>

</body>
</html>
