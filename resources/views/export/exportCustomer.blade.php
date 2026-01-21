
<body>
<div class="info">
    <strong>CỬA HÀNG: {{ $user->store_name }}</strong><br>
    SĐT: {{ $user->phone }}<br>
    Địa chỉ: {{ $user->address }}<br>
    Ngày xuất file: {{ \Carbon\Carbon::parse()->format('d/m/Y') }}<br>
</div>

<table>
    <thead>
    <tr>
        <th colspan="12" style="text-align: center; font-size: 18px;">BẢNG THÔNG TIN KHÁCH HÀNG</th>
    </tr>
    <tr>
        <th style="background-color: #ffff99;">STT</th>
        <th style="background-color: #ffff99;">Tên khách hàng</th>
        <th style="background-color: #ffff99;">Email</th>
        <th style="background-color: #ffff99;">Số điện thoại</th>
        <th style="background-color: #ffff99;">Địa chỉ</th>
        <th style="background-color: #ffff99;">Thông tin chi tiết</th>
        <th style="background-color: #ffff99;">Ngày tạo</th>
    </tr>
    </thead>
    <tbody>
    @foreach($customers as $index => $customer)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->email }}</td>
            <td>{{ $customer->phone }}</td>
            <td>{{ $customer->address }}</td>
            <td>{{ $customer->note }}</td>
            <td>{{ $customer->created_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>

