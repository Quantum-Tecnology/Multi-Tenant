<?php

declare(strict_types=1);

use QuantumTecnology\Tenant\Support\TenantManager;
use QuantumTecnology\Tenant\TenantServiceProvider;

it('registers TenantManager as singleton and merges config', function (): void {
    $provider = new TenantServiceProvider(app());
    $provider->register();

    $a = app(TenantManager::class);
    $b = app(TenantManager::class);

    expect($a)->toBeInstanceOf(TenantManager::class)
        ->and(spl_object_id($a))->toBe(spl_object_id($b))
        ->and(config('tenant.table.progress'))->toBe('tenant_migrations_progress');
});

it('boots without errors (queue hooks)', function (): void {
    $provider = new TenantServiceProvider(app());
    $provider->boot();
    expect(true)->toBeTrue();
});
