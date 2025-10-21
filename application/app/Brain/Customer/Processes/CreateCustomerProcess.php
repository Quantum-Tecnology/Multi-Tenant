<?php

declare(strict_types=1);

namespace App\Brain\Customer\Processes;

use App\Brain\Customer\Tasks\Create02Task;
use App\Brain\Customer\Tasks\CreateTask;
use App\Brain\Customer\Tasks\CustomerJobTask;
use Brain\Process;

class CreateCustomerProcess extends Process
{
    protected array $tasks = [
        CreateTask::class,
        CustomerJobTask::class,
        Create02Task::class,
    ];
}
