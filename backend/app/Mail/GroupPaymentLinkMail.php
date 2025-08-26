<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GroupPaymentLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $memberName;
    public int $amount;
    public string $payUrl;
    public ?string $extraMsg;

    public function __construct(string $subject, string $memberName, int $amount, string $payUrl, ?string $extraMsg = null)
    {
        $this->subject($subject);
        $this->memberName = $memberName;
        $this->amount = $amount;
        $this->payUrl = $payUrl;
        $this->extraMsg = $extraMsg;
    }

    public function build()
    {
        return $this->view('emails.group_payment_link');
    }
}
