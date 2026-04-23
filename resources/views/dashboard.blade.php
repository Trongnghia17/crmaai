<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: #f4f6f9;
        }

        .card {
            border-radius: 16px;
            height: 100%;
        }

        .card-header {
            font-weight: bold;
        }

        .table img {
            width: 40px;
            border-radius: 6px;
        }

        .check-in-box {
            border: 1px solid #198754 !important;
        }

        .check-out-box {
            border: 1px solid #ff0000 !important;
        }
    </style>
</head>

<body>

    <div class="container-fluid py-4">

        <div class="row g-4">

            <!-- 🟦 GÓC 1: Chấm công mới -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <p>📸 10 chấm công mới nhất</p>
                        <a href="/attendance_detail" class="btn btn-sm btn-primary">Xem chi tiết</a>

                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Tên nhân viên</th>
                                    <th>Ngày</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Ảnh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendances as $item)
                                    <tr>
                                        <td>{{ $item->user->name ?? '' }}</td>
                                        <td>{{ $item->work_date->format('d/m/Y') }}</td>
                                        <td>
                                            {{ $item->check_in_time ? $item->check_in_time->addHours(7)->format('H:i') : '---' }}
                                        </td>
                                        <td>
                                            {{ $item->check_out_time ? $item->check_out_time->addHours(7)->format('H:i') : '---' }}
                                        </td>
                                        <td>
                                            @if ($item->check_in_image)
                                                <img class="check-in-box"
                                                    src="{{ asset('storage/' . $item->check_in_image) }}"
                                                    onclick="showImage(this.src)">
                                            @endif
                                            @if ($item->check_out_image)
                                                <img class="check-out-box"
                                                    src="{{ asset('storage/' . $item->check_out_image) }}"
                                                    onclick="showImage(this.src)">
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

            <!-- 🟩 GÓC 2: Nhân viên -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between">
                        👤 Nhân viên
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            + Thêm
                        </button>
                    </div>

                    <div class="card-body table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Tên</th>
                                    <th>Email</th>
                                    <th>Công ty</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->company->name ?? '---' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#editUserModal"
                                                onclick="setEditUser({{ $user }})">
                                                Sửa
                                            </button>

                                            <a href="/users/delete/{{ $user->id }}"
                                                onclick="return confirm('Xóa user này?')" class="btn btn-sm btn-danger">
                                                Xóa
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- 🟨 GÓC 3: Công ty -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between">
                        🏢 Công ty
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
                            + Thêm
                        </button>
                    </div>

                    <div class="card-body table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Tên</th>
                                    <th>Lat</th>
                                    <th>Lng</th>
                                    <th>Bán kính</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($companies as $c)
                                    <tr>
                                        <td>{{ $c->name }}</td>
                                        <td>{{ $c->lat }}</td>
                                        <td>{{ $c->lng }}</td>
                                        <td>{{ $c->radius }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#editCompanyModal"
                                                onclick="setEditCompany({{ $c }})">
                                                Sửa
                                            </button>

                                            <a href="/companies/delete/{{ $c->id }}"
                                                onclick="return confirm('Xóa công ty này?')"
                                                class="btn btn-sm btn-danger">
                                                Xóa
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 🟥 GÓC 4: Placeholder -->
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header">📊 Tổng quan hôm nay</div>

                    <div class="row text-center p-3">
                        <div class="col-6 mb-3">
                            <div class="card p-3 bg-success text-white">
                                <h4>{{ $todayCheckin }}</h4>
                                <small>Đã check-in</small>
                            </div>
                        </div>

                        <div class="col-6 mb-3">
                            <div class="card p-3 bg-warning text-dark">
                                <h4>{{ $notCheckin }}</h4>
                                <small>Chưa check-in</small>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card p-3 bg-dark text-white">
                                <h4>{{ $totalCompanies }}</h4>
                                <small>Công ty</small>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card p-3 bg-primary text-white">
                                <h4>{{ $totalUsers }}</h4>
                                <small>Nhân viên</small>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Modal Thêm User -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="/users/store">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">➕ Thêm nhân viên</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label>Tên</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Công ty</label>
                            <select name="company_id" class="form-control">
                                <option value="">-- Chọn công ty --</option>
                                @foreach ($companies as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success">Lưu</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- Modal Sửa User -->
    <div class="modal fade" id="editUserModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" id="editForm">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">✏️ Sửa nhân viên</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label>Tên</label>
                            <input type="text" name="name" id="edit_name" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Email (không thể chỉnh sửa)</label>
                            <input type="email" name="email" id="edit_email" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label>Mật khẩu (để trống nếu không đổi)</label>
                            <input type="text" name="password" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Công ty</label>
                            <select name="company_id" id="edit_company" class="form-control">
                                @foreach ($companies as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button class="btn btn-primary">Cập nhật</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
    <script>
        function setEditUser(user) {
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_company').value = user.company_id;

            document.getElementById('editForm').action = '/users/update/' + user.id;
        }
    </script>

    <!-- Modal xem ảnh -->
    <div class="modal fade" id="imageModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-body text-center">
                    <img id="previewImage" src="" style="width:100%; border-radius:10px;">
                </div>

            </div>
        </div>
    </div>

    <script>
        function showImage(src) {
            document.getElementById('previewImage').src = src;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }
    </script>

    {{-- công ty --}}
    <div class="modal fade" id="addCompanyModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" action="/companies/store">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">➕ Thêm công ty</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <input class="form-control mb-2" name="name" placeholder="Tên công ty">
                        <input class="form-control mb-2" name="lat" placeholder="Latitude">
                        <input class="form-control mb-2" name="lng" placeholder="Longitude">
                        <input class="form-control mb-2" name="radius" placeholder="Bán kính (m)">

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success">Lưu</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <div class="modal fade" id="editCompanyModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <form method="POST" id="editCompanyForm">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">✏️ Sửa công ty</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <input id="c_name" class="form-control mb-2" name="name">
                        <input id="c_lat" class="form-control mb-2" name="lat">
                        <input id="c_lng" class="form-control mb-2" name="lng">
                        <input id="c_radius" class="form-control mb-2" name="radius">

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary">Cập nhật</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <script>
        function setEditCompany(c) {
            document.getElementById('c_name').value = c.name;
            document.getElementById('c_lat').value = c.lat;
            document.getElementById('c_lng').value = c.lng;
            document.getElementById('c_radius').value = c.radius;

            document.getElementById('editCompanyForm').action = '/companies/update/' + c.id;
        }
    </script>
</body>

</html>
