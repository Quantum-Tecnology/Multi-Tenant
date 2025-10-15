<?php

use Illuminate\Support\Str;
use QuantumTecnology\Tenant\Models\Tenant;
use QuantumTecnology\Tenant\Support\DefaultTenantConnectionResolver;

it('merges tenant data over base connection and returns fixed name', function () {
    $resolver = new DefaultTenantConnectionResolver();

    $tenant = new Tenant();
    $tenant->id = (string) Str::ulid();
    $tenant->data = [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => 'tenant_',
    ];

    $base = [
        'driver' => 'sqlite',
        'database' => 'ignored',
        'prefix' => '',
    ];

    $cfg = $resolver->buildConnectionConfig($tenant, $base);

    expect($cfg['database'])->toBe(':memory:')
        ->and($cfg['driver'])->toBe('sqlite')
        ->and($cfg['prefix'])->toBe('tenant_');

    expect($resolver->connectionName($tenant))->toBe('tenant');
});
