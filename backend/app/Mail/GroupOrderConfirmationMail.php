<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\GroupOrder;
use App\Models\GroupOrderMember;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GroupOrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $groupOrder;
    public $member;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, GroupOrder $groupOrder, GroupOrderMember $member)
    {
        $this->order = $order;
        $this->groupOrder = $groupOrder;
        $this->member = $member;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("Xác nhận đơn hàng nhóm #{$this->order->order_code}")
            ->markdown('emails.group_order.confirmation')
            ->with([
                'order' => $this->order,
                'groupOrder' => $this->groupOrder,
                'member' => $this->member,
            ]);
    }
}
