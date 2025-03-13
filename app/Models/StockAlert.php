<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_product',
        'threshold_quantity',
        'is_active'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }
}