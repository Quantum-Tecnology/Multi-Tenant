<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Support;

use Illuminate\Support\Facades\Config;
use QuantumTecnology\Tenant\Contracts\TenantEnvironmentApplier;
use QuantumTecnology\Tenant\Models\Tenant;

final class DefaultTenantEnvironmentApplier implements TenantEnvironmentApplier
{
    private ?string $originalCachePrefix = null;

    private ?string $originalRedisPrefix = null;

    public function apply(Tenant $tenant, string $connectionName): void
    {
        // Remember original prefixes so we can restore them in reset()
        $this->originalCachePrefix = (string) config('cache.prefix', '');
        $this->originalRedisPrefix = (string) config('database.redis.options.prefix', '');

        $basePrefix = $this->originalCachePrefix;
        if ($basePrefix !== '') {
            $basePrefix .= '_';
        }
        $tenantPrefix = $basePrefix.$tenant->id;

        // Set cache prefix used by most stores
        Config::set('cache.prefix', $tenantPrefix);

        // Additionally, for redis, set the low-level redis prefix too
        if (config('cache.default') === 'redis') {
            Config::set('database.redis.options.prefix', $tenantPrefix.':');
        }

        app()->instance('tenant', $tenant);
    }

    public function reset(): void
    {
        if ($this->originalCachePrefix !== null) {
            Config::set('cache.prefix', $this->originalCachePrefix);
        }
        if ($this->originalRedisPrefix !== null) {
            Config::set('database.redis.options.prefix', $this->originalRedisPrefix);
        }
    }
}
