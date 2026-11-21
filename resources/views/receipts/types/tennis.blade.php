{{-- Шаблон рахунку тенісного корту. --}}
<div class="receipt-type">Спортивне замовлення · Теніс</div>
<div class="receipt-details">
    @if($activity)<div class="detail-row"><span>Заняття</span><strong>{{ $activity }}</strong></div>@endif
    @if($trainer)<div class="detail-row"><span>Тренер</span><strong>{{ $trainer }}</strong></div>@endif
    @if($tableName)<div class="detail-row"><span>Корт</span><strong>{{ $tableName }}</strong></div>@endif
    @include('receipts.partials.period')
</div>
@include('receipts.partials.products')
