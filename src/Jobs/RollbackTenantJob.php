<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use QuantumTecnology\Tenant\Jobs\Enum\StatusEnum;
use QuantumTecnology\Tenant\Models\Tenant;
use QuantumTecnology\Tenant\Support\TenantManager;
use Throwable;

final class RollbackTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Tenant $tenant, public string $step)
    {
        $this->onQueue(config('tenant.queue.connection'));
    }

    public function handle(TenantManager $manager): void
    {
        $manager->switchTo($this->tenant);

        $paths = DB::table('migrations')
            ->where('batch', '>', $this->step)
            ->orderByDesc('id')
            ->pluck('migration')
            ->toArray();

        try {
            foreach ($paths as $migration) {
                Artisan::call('migrate:rollback', [
                    '--database' => 'tenant',
                    '--path' => "database/migrations/tenant/{$migration}.php",
                    '--force' => true,
                ]);
            }

            logger("↩️ Rollback feito em {$this->tenant->id} on the step {$this->step}");
        } catch (Throwable $e) {
            // Ensure we are back on the central connection before recording the failure
            $manager->disconnect();

            logger()->error("⚠️ Falha ao reverter {$this->tenant->id}: {$e->getMessage()}");

            DB::table(config('tenant.table.progress'))->insert([
                'tenant_id' => $this->tenant->id,
                'batch_id' => 'by-job',
                'status' => StatusEnum::ERROR_ON_ROLLBACK->value,
                'data' => $e->getMessage(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            throw $e;
        } finally {
            $manager->disconnect();
        }
    }
}
