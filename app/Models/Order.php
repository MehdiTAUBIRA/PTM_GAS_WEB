<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id_order';

    protected $fillable = [
        'order_number',
        'id_customer',
        'id_cus_address',
        'order_date',
        'delivery_date',
        'status',
        'total_amount',
        'comments'
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'delivery_date' => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    public function customerAddress()
    {
        return $this->belongsTo(CustomerAddress::class, 'id_cus_address', 'id_cus_address');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'id_order', 'id_order');
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class);
    }
}