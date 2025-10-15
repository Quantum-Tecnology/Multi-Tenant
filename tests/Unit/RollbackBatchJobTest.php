<?php

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use QuantumTecnology\Tenant\Jobs\RollbackBatchJob;
use QuantumTecnology\Tenant\Jobs\RollbackTenantJob;
use QuantumTecnology\Tenant\Models\Tenant;

it('dispatches rollback jobs for successful tenants', function () {
    // central sqlite is in memory by default; create tenants table
    Schema::create('tenants', function ($table) {
        $table->string('id')->primary();
        $table->json('data')->nullable();
        $table->timestamps();
    });

    $tenantId = (string) Str::ulid();
    // insert a tenant row
    \DB::table('tenants')->insert([
        'id' => $tenantId,
        'data' => json_encode([]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Bus::fake();

    $job = new RollbackBatchJob([$tenantId => '3']);
    $job->handle();

    Bus::assertDispatched(RollbackTenantJob::class, function ($job) use ($tenantId) {
        return $job->step === '3' && $job->tenant instanceof Tenant && $job->tenant->id === $tenantId;
    });
});
