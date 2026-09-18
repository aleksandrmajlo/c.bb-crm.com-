@extends('layouts.app')
@section('content')
        {!! $widget !!}
    {{--
        <script id="widget-wfp-script" language="javascript" type="text/javascript" src="https://secure.wayforpay.com/server/pay-widget.js"></script>
        <script type="text/javascript">    var wayforpay = new Wayforpay();
            var pay = function () {
                wayforpay.run({
                    merchantAccount: "test_merch_n1",
                    merchantDomainName: "www.market.ua",
                    // authorizationType: "SimpleSignature",
                    // merchantSignature: "flk3409refn54t54t*FNJRET",
                    orderReference: "DH783023",
                    orderDate: "1415379863",
                    amount: "1547.36",
                    currency: "UAH",
                    productName: "Процессор Intel Core i5-4670 3.4GHz",
                    productPrice: "1000",
                    productCount: "1",
                    clientFirstName: "Вася",
                    clientLastName: "Васечкин",
                    clientEmail: "some@mail.com",
                    clientPhone: "380631234567",
                    language: "UA"
                }, function (response) {				 			},
                    function (response) {		},
                    function (response) {			} 		);
            }
        </script>
        <button type="button" onclick="pay();">Оплатить</button>
            --}}

{{--


    <script defer async id="widget-wfp-script" language="javascript" type="text/javascript" onload="wfpInit()" src="https://secure.wayforpay.com/server/pay-widget.js"></script>
    <script type="text/javascript">
        var wayforpay = null;
        var wfpPay = function () {
            wayforpay.run({
                "merchantAccount": "test_merch_n1",
                "merchantDomainName": "https:\/\/google.com",
                "merchantSignature": "6762edc32bb6f2aa588c229eb310750a",
                "returnUrl": "http:\/\/localhost:8000\/examples\/returnUrl.php",
                "serviceUrl": "http:\/\/localhost:8000\/examples\/serviceUrl.php",
                "orderReference": "d9fd1a916dc3c0129a87b61024f83a75acdef38f",
                "orderDate": 1728202782,
                "amount": 0.01,
                "currency": "USD",
                "productName": ["test"],
                "productPrice": [0.01],
                "productCount": [1],
                "clientFirstName": "John",
                "clientLastName": "Dou",
                "clientCountry": "USA",
                "clientEmail": "john.dou@gmail.com",
                "clientPhone": "+12025550152"
            });
        }
        var wfpInit = function () {
            wayforpay = new Wayforpay();

            window.addEventListener("message", receiveMessage);

            function receiveMessage(event) {
                if (event.data == "WfpWidgetEventClose"       // при закрытии виджета пользователем
                    || event.data == "WfpWidgetEventApproved" // при успешном завершении операции
                    || event.data == "WfpWidgetEventDeclined" // при неуспешном завершении
                    || event.data == "WfpWidgetEventPending"  // транзакция на обработке
                ) {
                    console.log(event.data);
                }
            }
        }
    </script>
    <button class="btn btn-primary" type="button" onclick="wfpPay();">Pay</button>
    --}}
@endsection
