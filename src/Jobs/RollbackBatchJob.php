<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class RollbackBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly array $successfulTenants)
    {
        $this->onQueue(config('tenant.queue.name'));
    }

    public function handle(): void
    {
        logger(__('⚠️ Batch failed, starting global rollback...'));

        $model = config('tenant.model.tenant');
        foreach ($this->successfulTenants as $tenantId => $step) {
            dispatch(new RollbackTenantJob($model::find($tenantId), $step));
        }
    }
}
