<p>Chào {{ $memberName }},</p>
<p>Số tiền bạn cần thanh toán: <strong>{{ number_format($amount) }} VND</strong></p>
@if(!empty($extraMsg))
<p>{{ $extraMsg }}</p>
@endif
<p>Nhấn để thanh toán:</p>
<p><a href="{{ $payUrl }}">{{ $payUrl }}</a></p>
<p>Cảm ơn bạn.</p>
