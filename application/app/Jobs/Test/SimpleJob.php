<?php

declare(strict_types=1);

namespace App\Jobs\Test;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SimpleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        logger('Rolling non tenant: '.tenant()->id);

        Customer::create(['name' => 'Simple Job Customer 01: '.now()->toDateTimeString()]);
    }
}
