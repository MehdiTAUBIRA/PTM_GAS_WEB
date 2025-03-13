<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employee';
    protected $primaryKey = 'id_employee';

    protected $fillable = [
        'employee_code',
        'employee_firstname',
        'employee_lastname',
        'employee_email',
        'employee_phone',
        'employee_role',
        'id_depot',
        'hire_date',
        'status',
        'username',
        'password'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'hire_date' => 'date',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relation avec le dépôt
    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class, 'id_depot');
    }
}