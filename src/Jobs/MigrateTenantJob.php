<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use QuantumTecnology\Tenant\Actions\MigrateAction;
use QuantumTecnology\Tenant\Models\Tenant;
use QuantumTecnology\Tenant\Support\TenantManager;

final class MigrateTenantJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Tenant $tenant, private readonly bool $fresh = false, private readonly bool $seed = false)
    {
        //
    }

    public function handle(TenantManager $manager): void
    {
        app(MigrateAction::class)->execute(
            $this->tenant,
            $this->batch()->id,
            $this->fresh,
            $this->seed,
        );
    }
}
