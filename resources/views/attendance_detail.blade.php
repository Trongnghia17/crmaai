<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Danh sách chấm công</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .check-in-box {
            border: 1px solid #198754 !important;
        }

        .check-out-box {
            border: 1px solid #ff0000 !important;
        }
    </style>
</head>

<body>

    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-3">📊 Danh sách chấm công </h4>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm">Quay lại Dashboard</a>
        </div>

        <!-- FILTER -->
        <form method="GET" class="row g-2 mb-3">

            <div class="col-md-3">
                <input type="month" name="month" class="form-control" value="{{ $filters['month'] ?? '' }}">
            </div>

            <div class="col-md-3">
                <select name="company_id" class="form-control">
                    <option value="">-- Công ty --</option>
                    @foreach ($companies as $c)
                        <option value="{{ $c->id }}"
                            {{ ($filters['company_id'] ?? '') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <select name="user_id" class="form-control">
                    <option value="">-- Nhân viên --</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}"
                            {{ ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary w-100">Lọc</button>
                <button type="button" onclick="exportExcel()" class="btn btn-success w-100">
                    Xuất Excel
                </button>
            </div>

        </form>
        <!-- TABLE -->
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên</th>
                        <th>Ngày</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Ảnh</th>
                        <th>Số công</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
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
                                    <img class="check-in-box" src="{{ asset('storage/' . $item->check_in_image) }}"
                                        onclick="showImage(this.src)" width="40">
                                @endif
                                @if ($item->check_out_image)
                                    <img class="check-out-box" src="{{ asset('storage/' . $item->check_out_image) }}"
                                        onclick="showImage(this.src)" width="40">
                                @endif
                            </td>
                            <td>{{ $item->check_out_time ? 1 : 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
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

    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    <script>
        function exportExcel() {

            let table = document.querySelector("table");
            let rows = table.querySelectorAll("tbody tr");

            let data = [];

            // Header (bỏ cột ảnh)
            data.push(["STT", "Tên", "Ngày", "Check-in", "Check-out", "Số công"]);

            rows.forEach(row => {
                let cells = row.querySelectorAll("td");

                if (cells.length) {
                    data.push([
                        cells[0].innerText.trim(),
                        cells[1].innerText.trim(),
                        cells[2].innerText.trim(),
                        cells[3].innerText.trim(),
                        cells[4].innerText.trim(),
                        cells[6].innerText.trim() // bỏ cột ảnh (index 5)
                    ]);
                }
            });

            // Tạo workbook
            let wb = XLSX.utils.book_new();
            let ws = XLSX.utils.aoa_to_sheet(data);

            // 🎨 Style cơ bản
            ws['!cols'] = [{
                    wch: 6
                }, // STT
                {
                    wch: 20
                }, // Tên
                {
                    wch: 12
                }, // Ngày
                {
                    wch: 10
                }, // Check-in
                {
                    wch: 10
                }, // Check-out
                {
                    wch: 10
                } // Công
            ];

            // 👉 In đậm header
            let range = XLSX.utils.decode_range(ws['!ref']);
            for (let C = range.s.c; C <= range.e.c; ++C) {
                let cell = ws[XLSX.utils.encode_cell({
                    r: 0,
                    c: C
                })];
                if (cell) {
                    cell.s = {
                        font: {
                            bold: true
                        }
                    };
                }
            }

            // Append sheet
            XLSX.utils.book_append_sheet(wb, ws, "ChamCong");

            // Tên file theo tháng
            let monthInput = document.querySelector('input[name="month"]');
            let month = monthInput && monthInput.value ? monthInput.value : 'tat-ca';

            // Xuất file
            XLSX.writeFile(wb, `cham-cong-${month}.xlsx`);
        }
    </script>
</body>

</html>
