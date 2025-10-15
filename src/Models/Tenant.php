<?php

declare(strict_types=1);

namespace QuantumTecnology\Tenant\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Stancl\VirtualColumn\VirtualColumn;

final class Tenant extends Model
{
    use HasUlids, VirtualColumn;

    protected $fillable = [
        'name',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
