<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Actions;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use QuantumTecnology\Tenant\Jobs\Enum\StatusEnum;
use QuantumTecnology\Tenant\Models\Tenant;
use QuantumTecnology\Tenant\Support\TenantManager;
use Throwable;

final readonly class RollbackAction
{
    public function __construct(
        private TenantManager $manager,
    ) {}

    public function execute(
        Tenant $tenant,
        string $step,
        bool $console = false,
    ): void {
        $this->manager->switchTo($tenant);

        $paths = DB::table('migrations')
            ->where('batch', '>', $step)
            ->orderByDesc('id')
            ->pluck('migration')
            ->toArray();

        try {
            foreach ($paths as $migration) {
                Artisan::call('migrate:rollback', [
                    '--database' => 'tenant',
                    '--path' => config('tenant.database.migrations')."/{$migration}.php",
                    '--force' => true,
                ]);
            }

            tenantLogAndPrint(__('↩️ Rollback completed on tenant :id at step :step', ['id' => $tenant->id, 'step' => $step]), console: $console);
        } catch (Throwable $e) {
            // Ensure we are back on the central connection before recording the failure
            $this->manager->disconnect();

            DB::table(config('tenant.table.progress'))->insert([
                'tenant_id' => $tenant->id,
                'batch_id' => 'by-job',
                'status' => StatusEnum::ERROR_ON_ROLLBACK->value,
                'last_batch' => $step,
                'data' => $e->getMessage(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            tenantLogAndPrint(__('⚠️  Failed to rollback tenant :id: :message', ['id' => $tenant->id, 'message' => $e->getMessage()]), 'error', $console);

            throw $e;
        } finally {
            $this->manager->disconnect();
        }
    }
}
