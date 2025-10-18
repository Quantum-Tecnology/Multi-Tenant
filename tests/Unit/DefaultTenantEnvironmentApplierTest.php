<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use QuantumTecnology\Tenant\Models\Tenant;
use QuantumTecnology\Tenant\Support\TenantEnvironmentApply;

it('applies and resets environment changes per tenant', function (): void {
    Config::set('cache.prefix', 'app');
    Config::set('cache.default', 'redis');
    Config::set('database.redis.options.prefix', 'app:');

    $applier = new TenantEnvironmentApply();

    $tenant = new Tenant();
    $tenant->id = (string) Str::ulid();

    $applier->apply($tenant, 'tenant');

    expect(config('cache.prefix'))->toBe('app_'.$tenant->id)
        ->and(config('database.redis.options.prefix'))->toBe('app_'.$tenant->id.':')
        ->and(app()->has('tenant'))->toBeTrue();

    $applier->reset();

    expect(config('cache.prefix'))->toBe('app')
        ->and(config('database.redis.options.prefix'))->toBe('app:')
        ->and(app()->has('tenant'))->toBeFalse();
});
