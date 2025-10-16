<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use QuantumTecnology\Tenant\Jobs\Enum\StatusEnum;
use QuantumTecnology\Tenant\Jobs\MigrateTenantJob;
use QuantumTecnology\Tenant\Models\Tenant;
use QuantumTecnology\Tenant\Support\TenantManager;

function create_sqlite_file(string $name): string
{
    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$name;
    if (file_exists($path)) {
        @unlink($path);
    }
    touch($path);

    return $path;
}

it('runs tenant migration job and updates progress table to success', function (): void {
    // Configure central sqlite to a file
    $central = create_sqlite_file('central.sqlite');
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite', [
        'driver' => 'sqlite',
        'database' => $central,
        'prefix' => '',
    ]);

    // Central tables
    Schema::create('tenant_migrations_progress', function ($table): void {
        $table->string('tenant_id');
        $table->string('batch_id');
        $table->integer('status');
        $table->text('data')->nullable();
        $table->string('last_batch');
        $table->timestamps();
    });

    // Prepare tenant db file and its migrations table
    $tenantDb = create_sqlite_file('tenant.sqlite');

    // Create a tenant model
    $tenant = new Tenant();
    $tenant->id = (string) Str::ulid();
    $tenant->data = [
        'driver' => 'sqlite',
        'database' => $tenantDb,
        'prefix' => '',
    ];

    // Pre-create tenant migrations table and one row (batch 1)
    // We need to connect as the job will read from the tenant connection name 'tenant'
    // Simulate the same configuration the manager will use
    config()->set('database.connections.tenant', [
        'driver' => 'sqlite',
        'database' => $tenantDb,
        'prefix' => '',
    ]);
    DB::connection('tenant');
    Schema::connection('tenant')->create(config('database.migrations.table', 'migrations'), function ($table): void {
        $table->increments('id');
        $table->string('migration');
        $table->integer('batch');
    });
    DB::connection('tenant')->table('migrations')->insert([
        'migration' => '2024_01_01_000000_create_foo_table',
        'batch' => 1,
    ]);

    // Swap the Artisan facade root (Console Kernel) with a fake implementation to avoid mocking final classes
    app()->instance(Illuminate\Contracts\Console\Kernel::class, new class implements Illuminate\Contracts\Console\Kernel
    {
        public function handle($input, $output = null): void {}

        public function terminate($input, $status): void {}

        public function call($command, array $parameters = [], $outputBuffer = null)
        {
            return 0;
        }

        public function queue($command, array $parameters = []): void {}

        public function all()
        {
            return [];
        }

        public function output()
        {
            return '';
        }

        public function bootstrap(): void {}
    });

    $job = new MigrateTenantJob($tenant, false, false);

    // Run job
    $job->handle(app(TenantManager::class));

    // Assert progress updated
    $row = DB::table('tenant_migrations_progress')->where('tenant_id', $tenant->id)->first();
    expect($row)->not->toBeNull()
        ->and($row->status)->toBe(StatusEnum::SUCCESS->value)
        ->and($row->last_batch)->toBe('1');
});
