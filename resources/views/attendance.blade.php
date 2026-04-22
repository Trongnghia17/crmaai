<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chấm công</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f5f7fb;
            min-height: 100vh;
        }

        .card {
            border-radius: 16px;
        }

        .check-in-box{
            background-color: #198754 !important;
            color:#fff;
        }

        .check-out-box{
            background-color: #0d6efd !important;
            color:#fff;
        }

        video {
            border-radius: 12px;
            border: 2px solid #ddd;
            max-height: 280px;
            object-fit: cover;
        }

        .btn-action {
            border-radius: 12px;
            transition: 0.3s;
            font-weight: 500;
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .status-box {
            padding: 10px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
        }

        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            display: none;
        }

        /* Mobile */
        @media (max-width: 576px) {
            .container {
                padding: 20px 12px;
            }

            .card {
                padding: 20px !important;
            }

            h4 {
                font-size: 20px;
            }

            video {
                max-height: 220px;
            }

            .btn-action {
                padding: 14px;
                font-size: 16px;
            }

            .status-box {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

<!-- Loading -->
<div class="loading-overlay" id="loading">
    <div class="text-center">
        <div class="spinner-border text-primary"></div>
        <p class="mt-2">Đang xử lý...</p>
    </div>
</div>

<div class="container py-4">

    <div class="row justify-content-center">
        <div class="col-12 col-sm-8 col-md-5">

            <div class="card p-4 shadow">

                <div class="d-flex justify-content-between mb-3">
                    <h4>📸 Chấm công</h4>
                    <a href="/history">DS Chấm công</a>
                    <form method="POST" action="/logout">
                        @csrf
                        <button class="btn btn-sm btn-danger">Logout</button>
                    </form>
                </div>

                {{-- Thông báo --}}
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{-- Trạng thái --}}
                <div class="mb-3">
                    <div class="status-box check-in-box bg-light">
                        Check-in:
                        <strong>
                            {{ $attendance && $attendance->check_in_time 
                                ? \Carbon\Carbon::parse($attendance->check_in_time)->addHours(7)->format('d/m/Y H:i') 
                                : 'Chưa chấm công' 
                            }}
                        </strong>
                    </div>

                    <div class="status-box check-out-box bg-light mt-2">
                        Check-out:
                        <strong>
                            {{ $attendance && $attendance->check_out_time 
                                ? \Carbon\Carbon::parse($attendance->check_out_time)->addHours(7)->format('d/m/Y H:i') 
                                : 'Chưa chấm công' 
                            }}
                        </strong>
                    </div>
                </div>

                {{-- Camera --}}
                <div class="text-center mb-3">
                    <video id="video" width="100%" autoplay></video>
                </div>

                <canvas id="canvas" style="display:none;"></canvas>

                {{-- Button --}}
                <div class="d-grid gap-2">

                    <button id="btnCheckin"
                            class="btn btn-success btn-action"
                            onclick="capture('check-in')"
                            {{ $attendance && $attendance->check_in_time ? 'disabled' : '' }}>
                        <span class="btn-text">✅ Check In</span>
                    </button>

                    <button id="btnCheckout"
                            class="btn btn-primary btn-action"
                            onclick="capture('check-out')"
                            {{ !$attendance || !$attendance->check_in_time || ($attendance && $attendance->check_out_time) ? 'disabled' : '' }}>
                        <span class="btn-text">📤 Check Out</span>
                    </button>

                </div>

                <form id="form" method="POST">
                    @csrf
                    <input type="hidden" name="image" id="image">
                    <input type="hidden" name="lat" id="lat">
                    <input type="hidden" name="lng" id="lng">
                </form>

            </div>
        </div>
    </div>
</div>

<script>
// mở camera
navigator.mediaDevices.getUserMedia({ video: true })
    .then(stream => {
        document.getElementById('video').srcObject = stream;
    })
    .catch(() => {
        alert("Không truy cập được camera");
    });

// loading UI
function showLoading(button) {
    document.getElementById('loading').style.display = 'flex';

    document.querySelectorAll('.btn-action').forEach(btn => {
        btn.disabled = true;
    });

    if (button) {
        button.innerHTML = `
            <span class="spinner-border spinner-border-sm"></span> Đang xử lý...
        `;
    }
}

// chụp ảnh + GPS
function capture(type) {

    const button = event.target.closest('button');
    showLoading(button);

    navigator.geolocation.getCurrentPosition((pos) => {

        document.getElementById('lat').value = pos.coords.latitude;
        document.getElementById('lng').value = pos.coords.longitude;

        let video = document.getElementById('video');
        let canvas = document.getElementById('canvas');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        let ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);

        let image = canvas.toDataURL('image/png');
        document.getElementById('image').value = image;

        let form = document.getElementById('form');
        form.action = type === 'check-in' ? '/check-in' : '/check-out';
        form.submit();

    }, () => {
        alert("Không lấy được vị trí GPS");
        document.getElementById('loading').style.display = 'none';
    });
}
</script>

</body>
</html>