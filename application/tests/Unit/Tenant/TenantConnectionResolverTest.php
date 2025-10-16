<?php

declare(strict_types=1);

use App\Contracts\TenantConnectionResolver;
use App\Models\Tenant;
use App\Support\TenantManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\mock;

it('allows customizing the tenant connection via resolver', function (): void {
    // Arrange a fake tenant
    $tenant = new Tenant([
        'name' => 'ACME',
        'data' => [
            'database' => 'acme-db',
        ],
    ]);
    $tenant->id = 'tnnt_12345';

    // Base connection
    Config::set('database.default', 'mysql');
    Config::set('database.connections.mysql', [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'username' => 'root',
        'password' => '',
        'database' => 'central',
    ]);

    // Fake resolver that uses a custom connection name and tweaks config
    $resolver = new class implements TenantConnectionResolver
    {
        public function buildConnectionConfig(Tenant $tenant, array $baseConnection): array
        {
            // Force sqlite memory for test stability and include tenant database name for verification
            return array_merge($baseConnection, [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'tenant_database' => $tenant->data['database'] ?? null,
            ]);
        }

        public function connectionName(Tenant $tenant): string
        {
            return 'tenant_custom';
        }

        public function applyEnvironment(Tenant $tenant, string $connectionName): void
        {
            // no-op for test
        }

        public function resetEnvironment(): void
        {
            // no-op for test
        }
    };

    // Mock DB facade so no real connection is attempted
    DB::shouldReceive('purge')->once()->with('tenant_custom');
    DB::shouldReceive('reconnect')->once()->with('tenant_custom');

    // Override resolver binding and resolve manager from container (uses default applier)
    app()->singleton(TenantConnectionResolver::class, fn () => $resolver);
    $manager = app(TenantManager::class);

    // Act
    $manager->switchTo($tenant);

    // Assert
    expect(Config::get('database.default'))->toBe('tenant_custom');
    expect(Config::get('database.connections.tenant_custom.driver'))->toBe('sqlite');
    expect(Config::get('database.connections.tenant_custom.tenant_database'))->toBe('acme-db');
    expect($manager->getTenant())->toBe($tenant);
});
