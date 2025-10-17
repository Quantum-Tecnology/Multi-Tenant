<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Models;

use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\Tenant\Models\Concerns\CentraConnection;
use QuantumTecnology\Tenant\Models\Concerns\GeneratesIds;
use Stancl\VirtualColumn\VirtualColumn;

class Tenant extends Model
{
    use CentraConnection, GeneratesIds, VirtualColumn;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'tenants';

    protected $primaryKey = 'id';

    protected $guarded = [];

    public static function getCustomColumns(): array
    {
        return [
            'id',
        ];
    }

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey(): int|string
    {
        return $this->getAttribute($this->getTenantKeyName());
    }
}
