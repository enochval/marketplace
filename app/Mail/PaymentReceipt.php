<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $amount;
    public $status;

    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $amount
     * @param $status
     */
    public function __construct($user, $amount, $status)
    {
        $this->user = $user;
        $this->amount = $amount;
        $this->status = $status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject("Timbala")
            ->markdown('emails.payment-receipt');
    }
}
