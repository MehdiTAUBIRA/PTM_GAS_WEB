<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';
    protected $primaryKey = 'id_customer';

    protected $fillable = [
        'customer_name',
        'customer_firstname',
        'customer_phone',
        'customer_email'
    ];

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'id_customer', 'id_customer');
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class);
    }
}