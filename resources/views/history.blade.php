<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử chấm công</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .card {
            border-radius: 16px;
        }

        .table img {
            width: 60px;
            border-radius: 8px;
            cursor: pointer;
        }

        .badge-status {
            font-size: 12px;
        }

        .check-in-img {
            border: 1px solid #00ff00;
        }

        .check-out-img {
            border: 1px solid #ff0000;
        }
    </style>
</head>

<body>

    <div class="container py-5">

        <div class="card shadow p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>📊 Lịch sử chấm công</h4>
                <a href="/" class="btn btn-success">Chấm công</a>
                <form method="GET" class="d-flex gap-2">
                    <input type="month" name="month" value="{{ $month }}" class="form-control">
                    <button class="btn btn-primary">Lọc</button>
                </form>
            </div>

            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>STT</th>
                        <th>Ngày</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Ảnh</th>
                        <th>Số công</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $totalWork = 0;
                    @endphp
                    @forelse($attendances as $index => $item)
                        @php
                            $checkIn = $item->check_in_time
                                ? \Carbon\Carbon::parse($item->check_in_time)->addHours(7)
                                : null;
                            $checkOut = $item->check_out_time
                                ? \Carbon\Carbon::parse($item->check_out_time)->addHours(7)
                                : null;

                            $workHours = 0;
                            $workDay = 0;

                            if ($checkIn && $checkOut) {
                                $workHours = $checkIn->diffInMinutes($checkOut) / 60;

                                if ($workHours >= 8) {
                                    $workDay = 1;
                                } else {
                                    $workDay = $workHours / 8;
                                }
                            }
                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>
                                {{ $item->work_date ? \Carbon\Carbon::parse($item->work_date)->format('d/m/Y') : '---' }}
                            </td>

                            <td>
                                {{ $checkIn ? $checkIn->format('H:i') : '---' }}
                            </td>

                            <td>
                                {{ $checkOut ? $checkOut->format('H:i') : '---' }}
                            </td>

                            <td class="d-flex gap-2">
                                @if ($item->check_in_image)
                                    <img class="check-in-img" src="{{ asset('storage/' . $item->check_in_image) }}"
                                        onclick="showImage(this.src)">
                                @endif

                                @if ($item->check_out_image)
                                    <img class="check-out-img" src="{{ asset('storage/' . $item->check_out_image) }}"
                                        onclick="showImage(this.src)">
                                @endif
                            </td>

                            <td>
                                @php
                                    $totalWork += $workDay;
                                @endphp
                            <td>
                                @if ($checkIn && $checkOut)
                                    <strong>{{ number_format($workDay, 2) }}</strong> công
                                    <br>
                                    {{-- <small class="text-muted">
                                        {{ round($workHours, 1) }} giờ
                                    </small> --}}
                                @else
                                    ---
                                @endif
                            </td>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Tổng công tháng:</th>
                        <th class="text-danger">
                            {{ number_format($totalWork, 2) }} công
                        </th>
                    </tr>
                </tfoot>
            </table>

        </div>
    </div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
