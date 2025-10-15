<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use QuantumTecnology\Tenant\Models\Concerns\GeneratesIds;
use Stancl\VirtualColumn\VirtualColumn;

class Tenant extends Model
{
    use VirtualColumn, GeneratesIds;

    protected $table = 'tenants';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey(): int|string
    {
        return $this->getAttribute($this->getTenantKeyName());
    }

    public function getKeyType(): string
    {
        return $this->shouldGenerateId() ? 'string' : $this->keyType;
    }
}
