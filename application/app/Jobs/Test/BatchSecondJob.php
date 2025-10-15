<?php

declare(strict_types=1);

namespace App\Jobs\Test;

use App\Models\Sector;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class BatchSecondJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        if ($this->batch()->canceled()) {
            return;
        }

        logger('Rolling non tenant o the second batch: '.tenant()->id);

        \Illuminate\Support\Facades\Cache::set('test_batch_second', 'oi');
    }
}
