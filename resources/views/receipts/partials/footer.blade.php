<section class="receipt-summary">
    @if($cashboxName)
        <div class="summary-row"><span>Спосіб оплати</span><span>{{ $cashboxName }}</span></div>
    @endif
    <div class="summary-row total"><span>Сума замовлення</span><span>{{ number_format($amount, 2, '.', ' ') }} {{ $currency }}</span></div>
</section>
<footer class="receipt-footer">
    <p>Сума враховує застосовані знижки та податки.</p>
    <p>Цей рахунок не є фіскальним чеком.</p>
</footer>
