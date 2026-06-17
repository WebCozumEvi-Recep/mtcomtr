<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SystemAlert extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'causer_id',
        'data'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_alert_statuses')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }
}
