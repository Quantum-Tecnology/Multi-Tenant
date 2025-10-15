<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use QuantumTecnology\Tenant\Models\Tenant;
use QuantumTecnology\Tenant\Support\TenantManager;

it('switches to tenant connection and disconnects back to original', function () {
    // Ensure a base connection exists
    Config::set('database.default', 'sqlite');
    Config::set('database.connections.sqlite', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);

    $manager = new TenantManager();

    $tenant = new Tenant();
    $tenant->id = (string) Str::ulid();
    $tenant->data = [
        'database' => ':memory:',
    ];

    $manager->switchTo($tenant);

    expect(config('database.default'))->toBe('tenant')
        ->and(app()->has('tenant'))->toBeTrue();

    $manager->disconnect();

    expect(config('database.default'))->toBe('sqlite')
        ->and(app()->has('tenant'))->toBeFalse();
});
