<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Support;

use Illuminate\Support\Facades\Config;
use QuantumTecnology\Tenant\Contracts\TenantQueueResolver;

final class TenantQueueApply implements TenantQueueResolver
{
    public function buildConnectionConfig(string $connect): void
    {
        Config::set([
            'queue.database.connection' => env('DB_QUEUE_CONNECTION', 'central'),
            'queue.batching.database' => 'central',
        ]);
    }
}
