<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $table = 'customer_address';
    protected $primaryKey = 'id_cus_address';

    protected $fillable = [
        'id_customer',
        'address',
        'postalcode',
        'country',
        'address_type'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

}