<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Depot extends Model
{

    use HasFactory;
    protected $table = 'depot';
    protected $primaryKey = 'id_depot';
    protected $fillable = [
        'depotpostalcode',
        'depotcode',
        'depotlabel',
        'depotaddress',
        'depotcity',
        'depotphone',
        'depotgpsx',
        'depotgpsy'
    ];

    public $timestamps = false;

    public function employees()
    {
        return $this->hasMany(Employee::class, 'id_depot');
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'id_depot');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'id_depot');
    }
}