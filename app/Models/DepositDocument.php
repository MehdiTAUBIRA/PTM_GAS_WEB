<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositDocument extends Model
{
    protected $table = 'deposit_documents';
    protected $primaryKey = 'id_deposit_document';

    protected $fillable = [
        'document_number',
        'document_type',
        'id_customer',
        'id_customer_deposit',
        'document_date',
        'amount',
        'document_url',
        'created_by'
    ];

    protected $casts = [
        'document_date' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    public function customerDeposit()
    {
        return $this->belongsTo(CustomerDeposit::class, 'id_customer_deposit', 'id_customer_deposit');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id_employee');
    }
}