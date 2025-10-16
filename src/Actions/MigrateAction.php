<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Actions;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use QuantumTecnology\Tenant\Jobs\Enum\StatusEnum;
use QuantumTecnology\Tenant\Models\Tenant;
use QuantumTecnology\Tenant\Support\TenantManager;
use Throwable;

final readonly class MigrateAction
{
    public function __construct(
        private TenantManager $manager,
    ) {}

    public function execute(
        Tenant $tenant,
        string $identifier,
        bool $fresh = false,
        bool $seed = false
    ): void {
        $this->manager->switchTo($tenant);

        $batch = DB::connection('tenant')
            ->table(config('database.migrations.table'))
            ->orderByDesc('id')
            ->first()
            ?->batch;

        $this->manager->disconnect();

        DB::table(config('tenant.table.progress'))->insert([
            'tenant_id' => $tenant->id,
            'batch_id' => $identifier,
            'status' => StatusEnum::PENDING->value,
            'last_batch' => $batch ?: '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('migrations')->where('id', '>', 13)->delete();

        $this->manager->switchTo($tenant);

        try {
            $params = [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ];

            if ($fresh) {
                Artisan::call('migrate:fresh', $params);
            } else {
                Artisan::call('migrate', $params);
            }

            if ($seed) {
                Artisan::call('db:seed', [
                    'class' => 'TenantSeeder',
                ]);
            }

            $this->manager->disconnect();

            tenantLogAndPrint("✅ Migração concluída no tenant {$tenant->id}");

            DB::table(config('tenant.table.progress'))->where([
                'tenant_id' => $tenant->id,
                'batch_id' => $identifier ?? 'manual',
            ])->update([
                'status' => StatusEnum::SUCCESS->value,
                'updated_at' => now(),
            ]);

        } catch (Throwable $e) {
            $this->manager->disconnect();
            tenantLogAndPrint("❌ Falha ao migrar tenant {$tenant->id}: {$e->getMessage()}", 'error');

            DB::table(config('tenant.table.progress'))->where([
                'tenant_id' => $tenant->id,
                'batch_id' => $identifier ?? 'manual',
            ])->update([
                'status' => StatusEnum::ERROR->value,
                'data' => $e->getMessage(),
                'updated_at' => now(),
            ]);

            throw $e;
        }
    }
}
