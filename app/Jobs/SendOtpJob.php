<?php

namespace App\Jobs;

use App\Modules\Massaging\Contracts\MessageSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendOtpJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $phone, protected $otp, protected $countryCode = '+967') {}

    /**
     * Execute the job.
     */
    public function handle(MessageSender $sender): void
    {
        $sender->send($this->phone, $this->otp, $this->countryCode);
    }
}
