<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Xác nhận đơn hàng #' . $this->order->order_code . ' - SmartBook App',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Tính toán các dữ liệu cần thiết cho view
        $totalQuantity = $this->order->orderItems->sum('quantity');
        
        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'order' => $this->order,
                'orderItems' => $this->order->orderItems,
                'user' => $this->order->user,
                'totalQuantity' => $totalQuantity,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}