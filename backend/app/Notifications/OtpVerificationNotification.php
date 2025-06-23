<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OtpVerificationNotification extends Notification
{
    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Mã xác thực tài khoản')
            ->line('Chào bạn!')
            ->line('Mã OTP để xác thực tài khoản của bạn là: ' . $this->otp)
            ->line('Mã này có hiệu lực trong 5 phút.')
            ->line('Nếu bạn không tạo tài khoản, vui lòng bỏ qua email này.');
    }
}