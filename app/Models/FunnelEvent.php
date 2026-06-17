<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder query()
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $operator = null, mixed $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder whereDate(string $column, mixed $operator = null, mixed $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder whereBetween(string $column, array $values)
 * @method static \Illuminate\Database\Eloquent\Builder whereMonth(string $column, mixed $value)
 * @method static \Illuminate\Database\Eloquent\Builder whereYear(string $column, mixed $value)
 * @property int $id
 * @property string $event_type
 */
class FunnelEvent extends Model
{
    protected $fillable = [
        'domain_id', 'session_id', 'event_type', 'event_value', 'ip', 'user_agent'
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}
