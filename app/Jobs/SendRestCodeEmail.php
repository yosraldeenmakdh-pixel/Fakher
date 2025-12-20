<?php

namespace App\Jobs;

use App\Mail\RestCodeMail;
use App\Models\RestCode;
use App\Services\QueueProcessorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendRestCodeEmail implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $code;

    public function __construct($user, $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        RestCode::create([
            'email' => $this->user->email,
            'code' => $this->code
        ]);

        Mail::to($this->user)->send(new RestCodeMail($this->code));

        // if (app(QueueProcessorService::class)->getQueueSize() > 0) {
        //     sleep(2); // انتظار 2 ثانية
        //     app(QueueProcessorService::class)->processIfNeeded();
        // }
    }
}
