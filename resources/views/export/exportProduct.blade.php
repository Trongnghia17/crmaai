
<body>
<div class="info">
    <strong>CỬA HÀNG: {{ $user->store_name }}</strong><br>
    SĐT: {{ $user->phone }}<br>
    Địa chỉ: {{ $user->address }}<br>
    Ngày xuất file: {{ \Carbon\Carbon::parse()->format('d/m/Y') }}<br>
</div>

<p style="text-align: center; font-weight: bold; font-size: 18px;">
    BẢNG THÔNG TIN SẢN PHẨM
</p>

<table>
    <thead>
    <tr>
        <th>STT</th>
        <th>Tên sản phẩm</th>
        <th>SKU</th>
        <th>Barcode</th>
        <th>Mô tả</th>
        <th>Trạng thái luôn bán</th>
        <th>Giá nhập</th>
        <th>Giá bán lẻ</th>
        <th>Giá bán buôn</th>
        <th>Số lượng tồn kho</th>
        <th>Số lượng đã bán</th>
        <th>Số lượng còn lại tạm thời</th>
        <th>Số lượng khả dụng</th>
        <th>Đơn vị</th>
    </tr>
    </thead>
    <tbody>
    @foreach($products as $index => $product)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->sku }}</td>
            <td style="text-align: left;">{{ $product->barcode }}</td>
            <td>{{ $product->description }}</td>
            <td>{{ $product->is_buy_always ? 'Có' : 'Không' }}</td>
            <td>{{ number_format($product->base_cost, 0, ',', '.') }}</td>
            <td>{{ number_format($product->retail_cost, 0, ',', '.') }}</td>
            <td>{{ number_format($product->wholesale_cost, 0, ',', '.') }}</td>
            <td>{{ $product->in_stock }}</td>
            <td>{{ $product->sold }}</td>
            <td>{{ $product->temporality }}</td>
            <td>{{ $product->available }}</td>
            <td>{{ $product->unit }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>

