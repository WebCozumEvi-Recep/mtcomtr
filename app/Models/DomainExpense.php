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
 * @method static mixed sum(string $column)
 * @property int $id
 * @property float $amount
 * @property \Carbon\Carbon $spent_at
 */
class DomainExpense extends Model
{
    protected $fillable = [
        'domain_id',
        'platform',
        'amount',
        'spent_at',
        'description'
    ];

    protected $casts = [
        'spent_at' => 'date',
        'amount' => 'decimal:2'
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}
