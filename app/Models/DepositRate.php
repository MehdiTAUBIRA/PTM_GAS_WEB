<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositRate extends Model
{
    protected $table = 'deposit_rates';
    protected $primaryKey = 'id_deposit_rate';

    protected $fillable = [
        'id_product',
        'deposit_amount',
        'valid_from',
        'is_active'
    ];

    protected $casts = [
        'deposit_amount' => 'decimal:2',
        'valid_from' => 'date',
        'is_active' => 'boolean'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_product');
    }
}