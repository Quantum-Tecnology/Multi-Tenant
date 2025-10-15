<?php

namespace QuantumTecnology\Tenant\Models\Concerns;

use QuantumTecnology\Tenant\Contracts\UniqueIdentifierInterface;

trait GeneratesIds
{
    public static function bootGeneratesIds(): void
    {
        static::creating(function (self $model) {
            if (!$model->getKey() && $model->shouldGenerateId()) {
                $model->setAttribute($model->getKeyName(), app(UniqueIdentifierInterface::class)->generate($model));
            }
        });
    }

    public function getIncrementing(): bool
    {
        return ! app()->bound(UniqueIdentifierInterface::class);
    }

    public function shouldGenerateId(): bool
    {
        return ! $this->getIncrementing();
    }

    public function getKeyType()
    {
        return $this->shouldGenerateId() ? 'string' : $this->keyType;
    }
}