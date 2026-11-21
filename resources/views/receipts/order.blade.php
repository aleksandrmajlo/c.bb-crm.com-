<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Рахунок №{{ $order->id }}</title>
    @include('receipts.partials.styles')
</head>
<body>
    <main class="receipt-layout">
        <article class="receipt">
            @include('receipts.partials.header')
            @include('receipts.types.'.$receiptType)
            @include('receipts.partials.footer')
        </article>
        <aside class="receipt-actions" aria-label="Дії з рахунком">
            <span class="action-label">ВАШ РАХУНОК</span>
            <p>Збережіть рахунок або роздрукуйте його.</p>
            <button class="action action-print" type="button" data-print-receipt>Роздрукувати <span aria-hidden="true">↗</span></button>
            <a class="action action-download" href="{{ $downloadUrl }}">Завантажити PDF <span aria-hidden="true">↓</span></a>
        </aside>
    </main>
    <script src="{{ asset('js/checkbox/order-receipt.js') }}" defer></script>
</body>
</html>
