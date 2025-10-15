<?php

declare(strict_types=1);

return [
    'model' => [
        'tenant' => App\Models\Tenant::class,
        'id_generator' => QuantumTecnology\Tenant\Support\UniqueIdentifierGenerators\UUIDGenerator::class,
    ],
    'table' => [
        'progress' => env('TENANT_TABLE_PROGRESS', 'tenant_migrations_progress'),
    ],
];
