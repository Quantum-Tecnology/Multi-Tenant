<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use QuantumTecnology\Tenant\Actions\RollbackAction;
use QuantumTecnology\Tenant\Models\Tenant;

final class RollbackTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Tenant $tenant, public string $step)
    {
        $this->onQueue(config('tenant.queue.name'));
    }

    public function handle(): void
    {
        app(RollbackAction::class)->execute($this->tenant, $this->step);
    }
}
