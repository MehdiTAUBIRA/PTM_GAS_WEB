<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';
    protected $primaryKey = 'id_order_detail';

    protected $fillable = [
        'id_order',
        'id_product',
        'quantity',
        'unit_price',
        'created_at'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_product');
    }
}