<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực OTP - RetailEase</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        p {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
        }
        .otp-code {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #d9534f;
            background: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
            margin: 20px auto;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: #777;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Xác thực OTP</h1>
    <p>Xin chào <strong>{{ $data['name'] ?? $data['email'] }}</strong>,</p>
    <p>Cảm ơn bạn đã đăng ký sử dụng phần mềm <strong>RetailEase</strong>. Dưới đây là mã OTP để xác thực tài khoản của bạn:</p>
    <p class="otp-code">{{ $data['otp'] }}</p>
    <p>Vui lòng nhập khi xác thực mã này để hoàn tất đăng ký.</p>
    <p>Nếu bạn không đăng ký tài khoản RetailEase, vui lòng bỏ qua email này.</p>
    <div class="footer">
        <p>Trân trọng,</p>
        <p><strong>Đội ngũ bảo mật của RetailEase</strong></p>
    </div>
</div>
</body>
</html>
