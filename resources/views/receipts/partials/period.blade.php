@if($order->start)
    <div class="detail-row"><span>Час</span><strong>{{ \Carbon\Carbon::parse($order->start)->format('d.m.Y H:i') }} – {{ \Carbon\Carbon::parse($order->closed)->format('d.m.Y H:i') }}</strong></div>
@endif
