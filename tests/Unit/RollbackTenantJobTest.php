<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use QuantumTecnology\Tenant\Jobs\Enum\StatusEnum;
use QuantumTecnology\Tenant\Jobs\RollbackTenantJob;
use QuantumTecnology\Tenant\Models\Tenant;
use QuantumTecnology\Tenant\Support\TenantManager;

beforeEach(function () {
    // central default to sqlite file for stability
    $central = sys_get_temp_dir().DIRECTORY_SEPARATOR.'central_rb.sqlite';
    if (!file_exists($central)) {
        touch($central);
    }
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite', [
        'driver' => 'sqlite',
        'database' => $central,
        'prefix' => '',
    ]);

    // Ensure a clean progress table for error path assertions (drop and recreate to avoid schema drift between tests)
    if (Schema::hasTable('tenant_migrations_progress')) {
        Schema::drop('tenant_migrations_progress');
    }
    Schema::create('tenant_migrations_progress', function ($table) {
        $table->string('tenant_id');
        $table->string('batch_id');
        $table->integer('status');
        $table->text('data')->nullable();
        $table->string('last_batch')->nullable();
        $table->timestamps();
    });
});

function prepareTenantWithMigrations(string $dbName, array $batches): Tenant {
    $tenantDb = sys_get_temp_dir().DIRECTORY_SEPARATOR.$dbName;
    if (file_exists($tenantDb)) {
        @unlink($tenantDb);
    }
    touch($tenantDb);

    // Pre-configure connection the manager will reuse
    config()->set('database.connections.tenant', [
        'driver' => 'sqlite',
        'database' => $tenantDb,
        'prefix' => '',
    ]);

    Schema::connection('tenant')->create('migrations', function ($table) {
        $table->increments('id');
        $table->string('migration');
        $table->integer('batch');
    });

    foreach ($batches as $i => $batch) {
        DB::connection('tenant')->table('migrations')->insert([
            'migration' => sprintf('2024_01_0%d_000000_mock_%d', $i + 1, $batch),
            'batch' => $batch,
        ]);
    }

    $tenant = new Tenant();
    $tenant->id = (string) Str::ulid();
    $tenant->data = [
        'driver' => 'sqlite',
        'database' => $tenantDb,
        'prefix' => '',
    ];

    return $tenant;
}

it('rolls back migrations above a given step successfully', function () {
    $tenant = prepareTenantWithMigrations('tenant_rb_ok.sqlite', [1, 2, 3]);

    // Expect rollbacks to succeed: swap Console Kernel with a fake that returns 0 on call()
    app()->instance(\Illuminate\Contracts\Console\Kernel::class, new class implements \Illuminate\Contracts\Console\Kernel {
        public function handle($input, $output = null) {}
        public function terminate($input, $status) {}
        public function call($command, array $parameters = [], $outputBuffer = null) { return 0; }
        public function queue($command, array $parameters = []) {}
        public function all() { return []; }
        public function output() { return ''; }
        public function bootstrap() {}
    });

    $job = new RollbackTenantJob($tenant, '1');
    $job->handle(app(TenantManager::class));

    expect(app()->has('tenant'))->toBeFalse();
});

it('records error on rollback failure', function () {
    $tenant = prepareTenantWithMigrations('tenant_rb_fail.sqlite', [2]);

    // Swap Console Kernel with a fake that throws on call() to simulate failure
    app()->instance(\Illuminate\Contracts\Console\Kernel::class, new class implements \Illuminate\Contracts\Console\Kernel {
        public function handle($input, $output = null) {}
        public function terminate($input, $status) {}
        public function call($command, array $parameters = [], $outputBuffer = null) { throw new Exception('boom'); }
        public function queue($command, array $parameters = []) {}
        public function all() { return []; }
        public function output() { return ''; }
        public function bootstrap() {}
    });

    $job = new RollbackTenantJob($tenant, '1');

    expect(function () use ($job) {
        $job->handle(app(TenantManager::class));
    })->toThrow(Exception::class);

    $row = DB::table('tenant_migrations_progress')->where([
        'tenant_id' => $tenant->id,
        'batch_id' => 'by-job',
        'status' => StatusEnum::ERROR_ON_ROLLBACK->value,
    ])->first();

    expect($row)->not->toBeNull();
});
