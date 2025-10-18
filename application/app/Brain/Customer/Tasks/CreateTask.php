<?php

declare(strict_types=1);

namespace App\Brain\Customer\Tasks;

use App\Models\Customer;
use Brain\Task;

class CreateTask extends Task
{
    public function handle(): self
    {
        Customer::create(['name' => 'Customer 01']);

        return $this;
    }
}
