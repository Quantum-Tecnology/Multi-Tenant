<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use QuantumTecnology\Tenant\Contracts\TenantEnvironmentApplier;
use QuantumTecnology\Tenant\Models\Tenant;

final class DefaultTenantEnvironmentApplier implements TenantEnvironmentApplier
{
    public function apply(Tenant $tenant, string $connectionName): void
    {
        $prefix = when(config('cache.prefix'), fn (): string => config('cache.prefix').'_');
        if (in_array(config('cache.default'), ['redis', 'database'], true)) {
            Cache::setPrefix($prefix.$tenant->id.':');
        }

        app()->instance('tenant', $tenant);
    }

    public function reset(): void
    {
        app()->forgetInstance('tenant');

        Config::set('cache.prefix', env('CACHE_PREFIX', 'laravel_cache'));
        if (config('cache.default') === 'redis') {
            Config::set('database.redis.options.prefix', env('REDIS_PREFIX', ''));
        }
    }
}
