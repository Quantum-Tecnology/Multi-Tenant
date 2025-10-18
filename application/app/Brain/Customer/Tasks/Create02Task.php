<?php

declare(strict_types=1);

namespace App\Brain\Customer\Tasks;

use App\Models\Customer;
use Brain\Task;

class Create02Task extends Task
{
    public function handle(): self
    {
        Customer::create(['name' => 'Customer 02']);

        return $this;
    }
}
