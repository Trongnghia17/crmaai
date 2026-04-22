<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .form-control {
            border-radius: 10px;
        }

        .btn-login {
            border-radius: 10px;
            transition: 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
        }

        /* Mobile */
        @media (max-width: 576px) {
            body {
                align-items: flex-start;
                padding-top: 60px;
            }

            .login-card {
                padding: 20px !important;
                border-radius: 12px;
            }

            h4 {
                font-size: 20px;
            }

            .form-label {
                font-size: 14px;
            }

            .form-control {
                padding: 14px;
                font-size: 16px;
            }

            .btn-login {
                padding: 14px;
                font-size: 16px;
            }
        }
    </style>
</head>

<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-8 col-md-4">

            <div class="card login-card p-4">

                <h4 class="text-center mb-4">🔐 Đăng nhập</h4>

                <form method="POST" action="/login">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" name="email" class="form-control" placeholder="Nhập email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-login">
                        Đăng nhập
                    </button>

                    @if(session('error'))
                        <div class="alert alert-danger mt-3">
                            {{ session('error') }}
                        </div>
                    @endif

                </form>

            </div>

        </div>
    </div>
</div>

</body>
</html>