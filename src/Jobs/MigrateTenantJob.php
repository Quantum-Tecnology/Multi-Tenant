<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Jobs;

use Illuminate\Bus\Batchable;
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

final class MigrateTenantJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Tenant $tenant, private bool $fresh = false, private bool $seed = false)
    {
        //
    }

    public function handle(TenantManager $manager): void
    {
        $manager->switchTo($this->tenant);

        $batch = DB::connection('tenant')
            ->table(config('database.migrations.table'))
            ->orderByDesc('id')
            ->first()
            ?->batch;

        $manager->disconnect();

        DB::table('tenant_migrations_progress')->insert([
            'tenant_id' => $this->tenant->id,
            'batch_id' => $this->batch()?->id ?? 'manual',
            'status' => StatusEnum::PENDING->value,
            'last_batch' => $batch ?: '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $manager->switchTo($this->tenant);

        try {
            $params = [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ];

            if ($this->fresh) {
                Artisan::call('migrate:fresh', $params);
            } else {
                Artisan::call('migrate', $params);
            }

            if ($this->seed) {
                Artisan::call('db:seed', [
                    'class' => 'TenantSeeder',
                ]);
            }

            $manager->disconnect();

            logger("✅ Migração concluída no tenant {$this->tenant->id}");

            DB::table('tenant_migrations_progress')->where([
                'tenant_id' => $this->tenant->id,
                'batch_id' => $this->batch()?->id ?? 'manual',
            ])->update([
                'status' => StatusEnum::SUCCESS->value,
                'updated_at' => now(),
            ]);

        } catch (Throwable $e) {
            $manager->disconnect();
            logger()->error("❌ Falha ao migrar tenant {$this->tenant->id}: {$e->getMessage()}");

            DB::table('tenant_migrations_progress')->where([
                'tenant_id' => $this->tenant->id,
                'batch_id' => $this->batch()?->id ?? 'manual',
            ])->update([
                'status' => StatusEnum::ERROR->value,
                'data' => $e->getMessage(),
                'updated_at' => now(),
            ]);

            throw $e;
        }
    }
}
