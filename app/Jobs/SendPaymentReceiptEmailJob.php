<?php

namespace App\Jobs;

use App\Mail\PaymentReceipt;
use Illuminate\Support\Facades\Mail;

class SendPaymentReceiptEmailJob extends Job
{
    protected $user;
    protected $amount;
    protected $status;

    /**
     * Create a new job instance.
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->user)->send(new PaymentReceipt($this->user, $this->amount, $this->status));
    }
}
