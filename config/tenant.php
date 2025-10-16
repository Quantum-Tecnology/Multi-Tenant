<?php

declare(strict_types=1);

return [
    'model' => [
        'tenant' => QuantumTecnology\Tenant\Models\Tenant::class,
        'id_generator' => QuantumTecnology\Tenant\Support\UniqueIdentifierGenerators\UUIDGenerator::class,
    ],
    'database' => [
        'migrations' => 'database/migrations/tenant',
        'seeder' => 'TenantSeeder',
    ],
    'table' => [
        'progress' => env('TENANT_TABLE_PROGRESS', 'tenant_migrations_progress'),
    ],
    'queue' => [
        'name' => env('TENANT_QUEUE_NAME', 'default'),
    ],
];
