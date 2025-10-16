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
        bool $seed = false,
        bool $console = false,
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
                '--path' => config('tenant.database.path'),
                '--force' => true,
            ];

            if ($fresh) {
                Artisan::call('migrate:fresh', $params);
            } else {
                Artisan::call('migrate', $params);
            }

            if ($seed) {
                Artisan::call('db:seed', [
                    'class' => config('tenant.database.seeder'),
                ]);
            }

            $this->manager->disconnect();

            tenantLogAndPrint(__('✅  Migration completed on tenant :id', ['id' => $tenant->id]), console: $console);

            DB::table(config('tenant.table.progress'))->where([
                'tenant_id' => $tenant->id,
                'batch_id' => $identifier ?? 'manual',
            ])->update([
                'status' => StatusEnum::SUCCESS->value,
                'updated_at' => now(),
            ]);

        } catch (Throwable $e) {
            $this->manager->disconnect();
            tenantLogAndPrint(__('❌  Failed to migrate tenant :id: :message', ['id' => $tenant->id, 'message' => $e->getMessage()]), 'error', $console);

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
