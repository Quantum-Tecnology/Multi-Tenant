<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Jobs;

use QuantumTecnology\Tenant\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

readonly final class RollbackBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private array $successfulTenants)
    {
        //
    }

    public function handle(): void
    {
        logger(__('⚠️ Batch failed, starting global rollback...'));

        foreach ($this->successfulTenants as $tenantId => $step) {
            dispatch(new RollbackTenantJob(Tenant::find($tenantId), $step));
        }
    }
}
