@component('mail::message')
# Xin chào {{ $member->display_name }}

Đơn hàng nhóm của bạn đã được xác nhận thành công 🎉

**Mã đơn hàng:** {{ $order->order_code }}  
**Tổng tiền:** {{ number_format($order->total_price, 0, ',', '.') }}₫  
**Địa chỉ giao hàng:** {{ $order->address }}  
**Thanh toán:** {{ strtoupper($order->payment) }}

---

### Danh sách sản phẩm:
@foreach($order->orderItems as $item)
- {{ $item->book->title }} × {{ $item->quantity }} — {{ number_format($item->price * $item->quantity, 0, ',', '.') }}₫
@endforeach

---

Trạng thái hiện tại: **{{ strtoupper($order->status) }}**

Cảm ơn bạn đã tham gia đặt hàng cùng nhóm ❤️  
@endcomponent
