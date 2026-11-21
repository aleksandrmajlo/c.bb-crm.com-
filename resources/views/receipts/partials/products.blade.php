@if($products->isNotEmpty())
    <div class="receipt-items">
        @foreach($products as $product)
            <table class="item"><tr>
                <td><span class="item-detail">{{ $product->pivot->count }} × {{ number_format((float) $product->pivot->price, 2, '.', ' ') }} {{ $currency }}</span><br>{{ $product->title }}</td>
                <td class="item-price">{{ number_format((float) $product->pivot->price * (float) $product->pivot->count, 2, '.', ' ') }} {{ $currency }}</td>
            </tr></table>
        @endforeach
    </div>
@endif
