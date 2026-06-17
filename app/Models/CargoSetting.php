<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property string $carrier_name
 * @property string $api_username
 * @property string $api_password
 * @property string $api_cod_username
 * @property string $api_cod_password
 * @property bool $is_active
 * @property bool $is_test_mode
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class CargoSetting extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = [
        'carrier_name',
        'display_name',
        'api_username',
        'api_password',
        'api_cod_username',
        'api_cod_password',
        'api_customer_code',
        'api_key',
        'is_active',
        'is_test_mode'
    ];
}
