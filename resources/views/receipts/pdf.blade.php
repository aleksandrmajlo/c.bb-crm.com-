<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <title>Рахунок №{{ $order->id }}</title>
    @include('receipts.partials.styles', ['forPdf' => true])
</head>
<body class="pdf-document">
    <article class="receipt">
        @include('receipts.partials.header')
        @include('receipts.types.'.$receiptType)
        @include('receipts.partials.footer')
    </article>
</body>
</html>
