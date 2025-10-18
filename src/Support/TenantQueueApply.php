<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Support;

use Illuminate\Support\Facades\Config;
use QuantumTecnology\Tenant\Contracts\TenantQueueResolver;

final class TenantQueueApply implements TenantQueueResolver
{
    public function buildConnectionConfig(string $connect): void
    {
        // Pin all queue-related databases to the provided connection (default to 'central')
        $connection = $connect ?: env('DB_QUEUE_CONNECTION', 'central');

        Config::set([
            'queue.connections.database.connection' => env('DB_QUEUE_CONNECTION', $connection),
            'queue.batching.database' => env('DB_QUEUE_CONNECTION_BATCHING', $connection),
            'queue.failed.database' => env('DB_QUEUE_CONNECTION_FAILED', $connection),
        ]);
    }
}
