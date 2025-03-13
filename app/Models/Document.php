<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'documents';
    protected $primaryKey = 'id_document';

    protected $fillable = [
        'document_number',
        'document_type',
        'document_date',
        'id_order',
        'id_customer',
        'total_amount',
        'payment_status',
        'payment_date',
        'payment_method',
        'document_url',
        'comments'
    ];

    protected $casts = [
        'document_date' => 'datetime',
        'payment_date' => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer', 'id_customer');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }
}