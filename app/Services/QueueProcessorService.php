<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class QueueProcessorService
{

    public function __construct()
    {
        //
    }


    public function processIfNeeded($force = false)
    {
        // التحقق إذا كان هناك وظائف في الطابور
        $queueSize = $this->getQueueSize();

        // إذا لم يكن هناك وظائف ولا إجبار، لا تفعل شيء
        if ($queueSize === 0 && !$force) {
            return [
                'processed' => false,
                'message' => 'No jobs in queue'
            ];
        }

        // منع التشغيل المتكرر (كل 30 ثانية كحد أدنى)
        if ($this->isTooFrequent() && !$force) {
            return [
                'processed' => false,
                'message' => 'Processing too frequent'
            ];
        }

        return $this->processQueue();
    }

    private function getQueueSize()
    {
        // استخدام نظام الطابور في Laravel
        return Queue::size();
    }

    private function isTooFrequent()
    {
        $lastProcessed = Cache::get('last_queue_processed');
        return $lastProcessed && now()->diffInSeconds($lastProcessed) < 30;
    }

    private function processQueue()
    {
        try {
            Cache::put('last_queue_processed', now(), 2); // تخزين لمدة دقيقتين

            // معالجة الوظائف مع عدد ديناميكي
            $jobsToProcess = min($this->getQueueSize(), 10);

            Artisan::call('queue:work', [
                '--max-jobs' => $jobsToProcess,
                '--stop-when-empty' => true,
                '--timeout' => 45,
                '--queue' => 'default'
            ]);

            $output = Artisan::output();
            $remainingJobs = $this->getQueueSize();

            // Log::info("Queue processed: {$jobsToProcess} jobs, Remaining: {$remainingJobs}");

            return [
                'processed' => true,
                'jobs_processed' => $jobsToProcess,
                'remaining_jobs' => $remainingJobs,
                'output' => $output
            ];

        } catch (\Exception $e) {
            // Log::error('Queue processing failed: ' . $e->getMessage());

            return [
                'processed' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // دالة لمعالجة فورية (مباشرة بعد التسجيل)
    public function processImmediately()
    {
        return $this->processQueue();
    }


}
