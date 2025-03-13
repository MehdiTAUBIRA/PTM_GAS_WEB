<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'driver';
    protected $primaryKey = 'id_driver';

    protected $fillable = [
        'drivercode',
        'drivername',
        'driversurname',
        'driverlastmedicalvisit',
        'driverlastlicensecontrol',
        'driveraccreditation',
        'driveraddress',
        'driveraddressnext',
        'driverphone',
        'drivercity',
        'driverpostalcode',
        'drivercountry',
        'drivercomments',
        'driveremail',
        'drivermobile',
        'driverattestationdate',
        'drivernextmedicalvisit',
        'drivernextlicensecontrol',
        'driver_status',
        'username',
        'driverpassword'
    ];

    protected $casts = [
        'driverlastmedicalvisit' => 'datetime',
        'driverlastlicensecontrol' => 'datetime',
        'driverattestationdate' => 'datetime',
        'drivernextmedicalvisit' => 'datetime',
        'drivernextlicensecontrol' => 'datetime',
        'driver_status' => 'boolean'
    ];
}