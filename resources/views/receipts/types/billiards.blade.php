{{-- Шаблон рахунку більярду. --}}
<div class="receipt-type">Замовлення більярду</div>
<div class="receipt-details">
    @if($tableName)<div class="detail-row"><span>Стіл</span><strong>{{ $tableName }}</strong></div>@endif
    @include('receipts.partials.period')
</div>
@include('receipts.partials.products')
