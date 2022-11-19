<?php

namespace Easy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip',
        'action',
        'model',
        'attributes',
        'changes',
        'logable_type',
        'logable_id'
    ];

    /**
     * Get the owning logable model.
     */
    public function logable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'));
    }
    
    public function getTable()
    {
        return config('easy.tables.logs','easy_logs');
    }
}
