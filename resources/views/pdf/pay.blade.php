<!DOCTYPE html>
<html>
<head>
    <title>Laravel PDF Example</title>
    <style>
        body {
            font-size: 14px;
            font-family: 'DejaVu Sans', sans-serif;
        }
        .border_bottom{
            border-bottom: 2px solid #000000;
        }

        table.borderTable{
            border-collapse: collapse;
            width: 100%;
            border: 1px solid black;
        }
        table.borderTable th,
        table.borderTable td{
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            font-size: 13px;
        }

        .totalTable td{
            padding: 8px;
        }
        .wrapObr{
            padding: 5px;
            border: 1px solid black;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="wrap_pay_conteer">
    <p class="border_bottom" style="font-weight: bold;">Рахунок-фактура № {{$id}} від {{$date}}</p>

    <table style="margin-bottom: 10px;">
        <tr>
            <td style="width: 150px;">Постачальник:</td>
            <td>{{$recipient['title']}}</td>
        </tr>
        <tr>
            <td style="width: 150px;"></td>
            <td>{{$recipient['phone']}}</td>
        </tr>
    </table>

    <table style="margin-bottom: 10px;">
        <tr>
            <td style="width: 150px;">Платник:</td>
            <td>{{$payer['title']}}</td>
        </tr>
        <tr>
            <td style="width: 150px;"></td>
            <td>{{$payer['phone']}}</td>
        </tr>
    </table>

    <table style="margin-bottom: 40px;">
        <tr>
            <td style="width: 150px;">Оплатити до:</td>
            <td>{{$end_pay}}</td>
        </tr>

    </table>

    <table class="borderTable" style="width: 100%;">
        <tr style="background: #9ca3af;">
            <td style="width: 10%;">№ </td>
            <td style="width: 50%;">Найменування товару або послуги</td>
            <td  style="width: 10%;">Кількість </td>
            <td style="width: 20%;">Ціна, ₴  </td>
            <td style="width: 20%;">Сума, ₴  </td>
        </tr>
        @foreach($orders as $order)
            <tr>
                <td style="width: 10%;">{{$order['id']}}</td>
                <td style="width: 60%;">
                    {{$order['title']}}
                    @if($order['subtitle'])<br/> {{$order['subtitle']}}@endif
                </td>
                <td  style="width: 10%;">{{$order['count']}}</td>
                <td style="width: 10%;">{{$order['price']}}</td>
                <td style="width: 10%;text-align: right;">{{$order['total']}}</td>
            </tr>
        @endforeach
    </table>

    <table class="totalTable" style="width: 100%;">
        <tr>
            <td style="width: 50%;font-weight: bold;text-align: right;">
                <span>Разом </span>
            </td>
            <td style="width: 50%;font-weight: bold;text-align: right;">
                {{$total}} ₴
            </td>
        </tr>
    </table>

    <h2 style="text-align: center;margin-bottom: 20px;">Зразок заповнення платіжного доручення</h2>

    <div class="wrapObr">

        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;text-align: center;">
                    <p>Отримувач {{$recipient['title']}}</p>
                    <p>Код {{$code}}</p>
                    <p>Призначення платежу:</p>
                    <p style="font-weight: bold;">{{$purpose_payment}}</p>
                </td>
                <td style="width: 50%;text-align: center;">
                    <p>КРЕДИТ рах. №</p>
                    <p style="font-weight: bold;">{{$numer_rach}}</p>
                </td>
            </tr>
        </table>

    </div>

    <div class="wrapQr" >
        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code">
    </div>


</div>
</body>
</html>
