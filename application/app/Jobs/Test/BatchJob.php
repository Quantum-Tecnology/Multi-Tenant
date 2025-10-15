<?php

declare(strict_types=1);

namespace App\Jobs\Test;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class BatchJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        if ($this->batch() && $this->batch()->canceled()) {
            return;
        }

        logger('Rolling non tenant on the batch job: '.tenant()->id);
        $this->batch()->add(new BatchSecondJob());

        \Illuminate\Support\Facades\Cache::set('test_batch', 'oi');
    }
}
