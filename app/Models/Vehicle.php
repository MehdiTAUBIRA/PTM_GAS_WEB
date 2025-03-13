<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $table = 'vehicle';
    protected $primaryKey = 'id_vehicle';

    protected $fillable = [
        'vehiclecode',
        'vehiclename',
        'vehicleimmat',
        'vehiclelastcontroldate',
        'vehiclenextcontroldate',
        'vehicletotalcapacity',
        'vehiclecontractnumber',
        'vehiclecontractlabel',
        'vehiclecontractdatestart',
        'vehiclecontractdateend',
        'vehicleweigth',
        'vehiclemaxweigth',
        'id_driver',
        'id_depot'
    ];

    protected $casts = [
        'vehiclelastcontroldate' => 'datetime',
        'vehiclenextcontroldate' => 'datetime',
        'vehiclecontractdatestart' => 'datetime',
        'vehiclecontractdateend' => 'datetime',
        'vehicletotalcapacity' => 'float',
        'vehicleweigth' => 'float',
        'vehiclemaxweigth' => 'float'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_driver', 'id_driver');
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class, 'id_depot', 'id_depot');
    }
}