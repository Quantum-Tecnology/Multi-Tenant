<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use QuantumTecnology\Tenant\Models\Tenant;
use QuantumTecnology\Tenant\Support\TenantManager;

final class IdentifyTenantMiddleware
{
    public function handle($request, Closure $next)
    {
        $domain = $request->getHost();
        $tenant = Tenant::query()->where('domain', $domain)->first();

        abort_unless((bool) $tenant, 404, 'Tenant not found.');

        app(TenantManager::class)->switchTo($tenant);

        return $next($request);
    }
}
