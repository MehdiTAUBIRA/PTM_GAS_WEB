<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDeposit extends Model
{
    protected $table = 'customer_deposits';
    protected $primaryKey = 'id_customer_deposit';

    protected $fillable = [
        'id_customer',
        'id_product',
        'deposit_paid',
        'deposit_amount',
        'deposit_date',
        'deposit_payment_method',
        'id_order',
        'id_employee',
        'comments'
    ];

    protected $casts = [
        'deposit_paid' => 'boolean',
        'deposit_amount' => 'decimal:2',
        'deposit_date' => 'datetime'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_product');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'id_employee', 'id_employee');
    }

    public function documents()
    {
        return $this->hasMany(DepositDocument::class, 'id_customer_deposit', 'id_customer_deposit');
    }
}