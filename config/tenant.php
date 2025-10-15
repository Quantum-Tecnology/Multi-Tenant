<?php

declare(strict_types=1);

return [
    'model' => QuantumTecnology\Tenant\Models\Tenant::class,
    'table' => [
        'progress' => env('TENANT_TABLE_PROGRESS', 'tenant_migrations_progress'),
    ],
];
