<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Commands;

use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use QuantumTecnology\Tenant\Actions\MigrateAction;
use QuantumTecnology\Tenant\Actions\RollbackAction;
use QuantumTecnology\Tenant\Jobs\Enum\StatusEnum;
use QuantumTecnology\Tenant\Jobs\MigrateTenantJob;
use QuantumTecnology\Tenant\Jobs\RollbackBatchJob;
use QuantumTecnology\Tenant\Models\Tenant;
use Throwable;

final class TenantMigrateCommand extends Command
{
    protected $signature = 'quantum:tenant-migrate
                            {--tenant_id= : Specific tenant ID (optional)}
                            {--fresh : Run migrate:fresh for each tenant}
                            {--seed : Run seeders after migrate for each tenant}';

    protected $description = 'Command description';

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

        if (config('queue.default') === 'sync') {
            $this->runCommandBySync($tenants);

            return self::SUCCESS;
        }

        $this->runCommandByJob($tenants);

        return self::SUCCESS;
    }

    private function runCommandByJob(Collection $tenants): void
    {
        $jobs = $tenants->map(fn ($tenant): MigrateTenantJob => new MigrateTenantJob(
            $tenant,
            $this->option('fresh'),
            $this->option('seed')
        ))->all();

        $batch = Bus::batch($jobs)
            ->then(function (): void {
                tenantLogAndPrint(__('✅ Batch completed successfully.'));
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
                        tenantLogAndPrint('❌ '.__(' Batch failed, rolling back already migrated tenants (:total total).', [
                            'total' => count($successful),
                        ]));
                        dispatch(new RollbackBatchJob($successful));
                    }

                    tenantLogAndPrint(__('🏁 Migration process finished.'));
                }
            })
            ->onQueue(config('tenant.queue.name'))
            ->dispatch();

        tenantLogAndPrint(__('🚀 Batch started. ID: :id', ['id' => $batch->id]));
    }

    private function runCommandBySync(Collection $tenants): void
    {
        $model = config('tenant.model.tenant');
        $id = (string) str()->uuid();

        $error = collect();

        $tenants->each(function (Tenant $tenant) use ($id, $error): void {
            try {
                app(MigrateAction::class)->execute(
                    $tenant,
                    $id,
                    $this->option('fresh'),
                    $this->option('seed'),
                    true
                );
            } catch (Throwable $e) {
                $error->push($tenant);
                report($e);
            }
        });

        if ($error->count() !== 0) {
            tenantLogAndPrint(__('⚠️ Some tenants failed during migration: :total', [
                'total' => $error->count(),
            ]), 'warning');

            $exist = DB::table(config('tenant.table.progress'))
                ->where('batch_id', $id)
                ->where('status', StatusEnum::ERROR->value)
                ->exists();

            if ($exist) {
                $successful = DB::table(config('tenant.table.progress'))
                    ->where('batch_id', $id)
                    ->pluck('last_batch', 'tenant_id')
                    ->toArray();

                if (filled($successful)) {
                    tenantLogAndPrint('❌ '.__(' Batch failed, rolling back already migrated tenants (:total total).', [
                        'total' => count($successful),
                    ]));

                    foreach ($successful as $tenant => $step) {
                        app(RollbackAction::class)->execute(
                            $model::find($tenant),
                            (string) $step,
                            true
                        );
                    }
                }

                tenantLogAndPrint(__('🏁 Migration process finished.'));
            }
        }
    }
}
