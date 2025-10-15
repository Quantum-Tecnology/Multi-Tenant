<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Commands;

use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use QuantumTecnology\Tenant\Jobs\Enum\StatusEnum;
use QuantumTecnology\Tenant\Jobs\MigrateTenantJob;
use QuantumTecnology\Tenant\Jobs\RollbackBatchJob;
use Throwable;

final class MigrateCommand extends Command
{
    protected $signature = 'tenants:migrate
                            {--tenant_id= : Specific tenant ID (optional)}
                            {--fresh : Run migrate:fresh for each tenant}
                            {--seed : Run seeders after migrate for each tenant}';

    protected $description = 'Run tenant migrations in a batch with global rollback on failure.';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        $tenantId = $this->option('tenant_id');

        $model = config('tenant.model.tenant');
        $query = $model::query();
        if ($tenantId) {
            $keyName = (new $model())->getTenantKeyName();
            $query->where($keyName, $tenantId);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn(__('⚠️ No tenants found.'));

            return self::FAILURE;
        }

        $jobs = $tenants->map(fn ($tenant): MigrateTenantJob => new MigrateTenantJob(
            $tenant,
            $this->option('fresh'),
            $this->option('seed')
        ))->all();

        $batch = Bus::batch($jobs)
            ->then(function (): void {
                logger(__('✅ Batch completed successfully.'));
            })
            ->finally(function (Batch $batch): void {
                $exist = DB::table(config('tenant.table.progress'))
                    ->where('batch_id', $batch->id)
                    ->where('status', StatusEnum::ERROR->value)
                    ->exists();

                if ($exist) {
                    $successful = DB::table(config('tenant.table.progress'))
                        ->where('batch_id', $batch->id)
                        ->pluck('last_batch', 'tenant_id')
                        ->toArray();

                    if (filled($successful)) {
                        logger('❌ '.__('Batch failed, rolling back already migrated tenants (:total total).', [
                            'total' => count($successful),
                        ]));
                        dispatch(new RollbackBatchJob($successful));
                    }

                    logger(__('🏁 Migration process finished.'));
                }
            })
            ->dispatch();

        $this->info(__('🚀 Batch started. ID: :id', ['id' => $batch->id]));

        return self::SUCCESS;
    }
}
