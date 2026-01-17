<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, Courier, monospace, sans-serif;
            font-size: 20px;
            font-weight: bold !important;
            /*text-align: center;*/
        }
    </style>
</head>
<body>
<table cellspacing="0" cellpadding="0" style="width: 98%; border-collapse: collapse; margin-right: calc(2%);">
    <tbody>
    <tr>
        <td style="width: 60.0476%; padding-right: 5.4pt; padding-left: 5.4pt; vertical-align: top;">
            <table style="width:535.5pt;margin-left:-4.5pt;border-collapse:collapse;border:none;">
                <tbody>
                <tr>

                    <td style="width: 211.5pt;padding: 0in 5.4pt;vertical-align: top;">
                        <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;text-align:center;line-height:150%;'>
                            <strong><span style=''>Mẫu số 01 -
                                    TT</span></strong></p>
                        <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;text-align:center;line-height:normal;'>
                            <em><span style='font-size:13px;'>(Ban h&agrave;nh theo Th&ocirc;ng tư số 133/2016/TT-BTC Ng&agrave;y 26/08/2016 của Bộ T&agrave;i ch&iacute;nh)</span></em>
                        </p>
                        <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;text-align:center;line-height:normal;'>
                            <span style=''>&nbsp;</span></p>
                    </td>
                </tr>
                </tbody>
            </table>

            <div style="position: absolute; right: 50px; top:100px">
                <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;line-height:  normal;'>
                    <span style=''>Quyển số: ………….</span></p>
                <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;
                line-height:  normal;'><span style=''>Số: {{$customerDebt->id}}</span></p>
                <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;
                line-height:  normal;'><span style=''>Nợ: ...</span></p>
                <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;
                line-height:  normal;'><span style=''>Có: ...</span></p>
            </div>

            <p style='margin-top:0in;margin-right:0in;margin-bottom:5.0pt;margin-left:0in;font-size:11.0pt;text-align:center;line-height:normal;'>
                <strong><span style='font-size:19px;'>PHIẾU THU</span></strong></p>
            <p style='margin-top:0in;margin-right:0in;margin-bottom:8.0pt;margin-left:0in;font-size:11.0pt;
            text-align:center;line-height:normal;'><strong><em><span
                            style=''>Ng&agrave;y {{ date("d", strtotime($customerDebt->created_at)) }}
                            th&aacute;ng
                            {{ date("m", strtotime($customerDebt->created_at)) }}  năm
                            {{ date("Y", strtotime($customerDebt->created_at)) }} </span></em></strong></p>
            <p style='margin-top:0in;margin-right:0in;margin-bottom:8.0pt;margin-left:0in;font-size:11.0pt;text-align:center;line-height:normal;'>
                <strong><em><span style=''>&nbsp;</span></em></strong></p>
            <p style='margin-top:0in;margin-right:0in;margin-bottom:8.0pt;margin-left:0in;font-size:11.0pt;'><span
                    style=''>Họ t&ecirc;n người nộp tiền:&nbsp;&nbsp;&nbsp; {{$customerDebt->customer?->name}}</span></p>
            <p style='margin-top:0in;margin-right:0in;margin-bottom:8.0pt;margin-left:0in;font-size:11.0pt;
            '><span style=''>Địa
                    chỉ:</span>&nbsp; &nbsp; &nbsp; <span style=''>&nbsp; &nbsp;{{$customerDebt->customer?->address}}</span></p>
            <p style='margin-top:0in;margin-right:0in;margin-bottom:8.0pt;margin-left:0in;font-size:11.0pt;
            '><span style=''>L&yacute; do nộp:
                    {!! $customerDebt->note!!}</span></p>
            <p style='margin-top:0in;margin-right:0in;margin-bottom:8.0pt;margin-left:0in;font-size:11.0pt;
            '><span style=''>Số tiền: &nbsp;
                    &nbsp; &nbsp; &nbsp; <strong>{{ number_format($customerDebt->price) }} VND</strong></span></p>
            <p style='margin-top:0in;margin-right:0in;margin-bottom:8.0pt;margin-left:0in;font-size:11.0pt;
            '><span style=''>Viết bằng chữ:
                    <strong><em>{{\App\Helpers\Helpers::convert_number_to_words(round($customerDebt->price))}}.</em></strong></span>
            </p>
            <p style='margin-top:0in;margin-right:0in;margin-bottom:8.0pt;margin-left:0in;font-size:11.0pt;'><span
                    style=''>K&egrave;m theo: &hellip;&hellip;&hellip;.. chứng từ gốc&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Ng&agrave;y... th&aacute;ng .... năm......</span>
            </p>
            <p style='margin-top:0in;margin-right:0in;margin-bottom:8.0pt;margin-left:0in;font-size:11.0pt;'><span
                    style=''>&nbsp;</span></p>
            <table style="width:526.5pt;border-collapse:collapse;border:none;">
                <tbody>
                <tr>
                    <td style="width: 112.5pt;padding: 0in 5.4pt;vertical-align: top;">
                        <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;text-align:center;line-height:normal;'>
                            <strong><span style=''>Gi&aacute;m đốc&nbsp;</span></strong></p>
                        <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;text-align:center;line-height:normal;'>
                            <em><span style=''>(K&yacute;, họ t&ecirc;n, đ&oacute;ng dấu)</span></em></p>
                    </td>
                    <td style="width: 121.5pt;padding: 0in 5.4pt;vertical-align: top;">
                        <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;text-align:center;line-height:normal;'>
                            <strong><span style=''>Kế to&aacute;n</span></strong><span
                                style=''>&nbsp;<strong>trưởng</strong>&nbsp;</span></p>
                        <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;text-align:center;line-height:normal;'>
                            <em><span style=''>(K&yacute;, họ t&ecirc;n)</span></em></p>
                    </td>
                    <td style="width: 99pt;padding: 0in 5.4pt;vertical-align: top;">
                        <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;text-align:center;line-height:normal;'>
                            <strong><span style=''>Người nộp tiền</span></strong><span style=''><br> <em>(K&yacute;, họ t&ecirc;n)</em></span>
                        </p>
                    </td>
                    <td style="width: 121.5pt;padding: 0in 5.4pt;vertical-align: top;">
                        <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;text-align:center;line-height:normal;'>
                            <strong><span style=''>Người lập phiếu</span></strong><span style=''><br> <em>(K&yacute;, họ t&ecirc;n)</em></span>
                        </p>
                    </td>
                    <td style="width: 1in;padding: 0in 5.4pt;vertical-align: top;">
                        <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;text-align:center;line-height:normal;'>
                            <strong><span style=''>Thủ quỹ</span></strong></p>
                        <p style='margin-top:0in;margin-right:0in;margin-bottom:.0001pt;margin-left:0in;font-size:11.0pt;text-align:center;line-height:normal;'>
                            <em><span style=''>(K&yacute;, họ t&ecirc;n)</span></em></p>
                    </td>
                </tr>
                </tbody>
            </table>
            <p style='margin-top:0in;margin-right:0in;margin-bottom:8.0pt;margin-left:0in;font-size:11.0pt;text-align:center;'>
                <span style=''>&nbsp;</span></p>
            <p style='margin-top:0in;margin-right:0in;margin-bottom:8.0pt;margin-left:0in;font-size:11.0pt;text-align:center;'>
                <span style=''>&nbsp;</span></p>
            <p style='margin-top:0in;margin-right:0in;margin-bottom:8.0pt;margin-left:0in;font-size:11.0pt;text-align:center;'>
                <span style=''>&nbsp;</span></p>
            <table style="width: 100%; border-collapse: collapse; border: none;">
                <tbody>
                <tr>
                    <td style="width: 50%; padding: 5px; vertical-align: top;">
                        <strong>Đã nhận đủ số tiền (Số):</strong><br>

                    </td>
                    <td style="width: 50%; padding: 5px;"> {{ number_format($customerDebt->price, 0, ',', '.') }} VNĐ</td> {{-- Cột trống --}}
                </tr>
                <tr>
                    <td style="width: 50%; padding: 5px;"> <strong>Đã nhận đủ số tiền (Viết bằng chữ):</strong></td> {{-- Cột trống --}}
                    <td style="width: 50%; padding: 5px; vertical-align: top;">
                        {{ \App\Helpers\Helpers::convert_number_to_words(round($customerDebt->price)) }}.
                    </td>
                </tr>
                </tbody>
            </table>
            <p style='margin-top:0in;margin-right:0in;margin-bottom:3.0pt;margin-left:0in;font-size:11.0pt;line-height:normal;'>
                <span style=''>&nbsp;</span></p>
        </td>
        <td style="width: 39.7146%; padding-right: 5.4pt; padding-left: 5.4pt; vertical-align: top;"><br></td>
    </tr>
    </tbody>
</table>
<p style="bottom: 10px; right: 10px; position: absolute;"><br></p>
</body>
</html>


