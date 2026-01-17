
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
        <th colspan="12" style="text-align: center; font-size: 18px;">DANH SÁCH NHÀ CUNG CẤP</th>
    </tr>
    <tr>
        <th style="background-color: #ffff99;">STT</th>
        <th style="background-color: #ffff99;">Tên nhà cung cấp</th>
        <th style="background-color: #ffff99;">Người đại diện</th>
        <th style="background-color: #ffff99;">Email</th>
        <th style="background-color: #ffff99;">Số điện thoại</th>
        <th style="background-color: #ffff99;">Địa chỉ</th>
        <th style="background-color: #ffff99;">Ngày tạo</th>
    </tr>
    </thead>
    <tbody>
    @foreach($suppliers as $index => $supplier)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $supplier->name }}</td>
            <td>{{ $supplier->contact_person }}</td>
            <td>{{ $supplier->email }}</td>
            <td>{{ $supplier->phone }}</td>
            <td>{{ $supplier->address }}</td>
            <td>{{ $supplier->created_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>

