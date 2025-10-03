<?php

namespace App\Jobs;

use App\Mail\CodeMail;
use App\Models\Code;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendVerificationEmail implements ShouldQueue
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
        Code::create([
            'email' => $this->user->email,
            'code' => $this->code
        ]);


        Cache::put(
            "verification:{$this->user->email}",
            $this->code,
            now()->addMinutes(30)
        );


        Mail::to($this->user)->send(new CodeMail($this->code));
    }
}
