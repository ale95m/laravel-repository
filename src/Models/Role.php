<?php

namespace ale95m\Easy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string|mixed name
 */
class Role extends LogableModel
{
    use HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @inheritDoc
     */
    protected function getLogInfo(): array
    {
        return [
            trans('validation.attributes.name') => $this->name,
        ];
    }
}
