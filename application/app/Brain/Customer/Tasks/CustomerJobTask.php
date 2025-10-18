<?php

declare(strict_types=1);

namespace App\Brain\Customer\Tasks;

use App\Jobs\Test\BatchJob;
use App\Jobs\Test\SimpleJob;
use Brain\Task;
use Illuminate\Support\Facades\Bus;

class CustomerJobTask extends Task
{
    public function handle(): self
    {
        SimpleJob::dispatch();

        Bus::batch([
            new BatchJob(),
        ])->dispatch();

        return $this;
    }
}
