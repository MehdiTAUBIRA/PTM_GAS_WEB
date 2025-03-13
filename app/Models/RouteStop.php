<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    use HasFactory;

    protected $table = 'route_stops';
    protected $primaryKey = 'id_route_stop';
    
    protected $fillable = [
        'id_route_stop',
        'id_route',
        'id_order',
        'stop_order',
        'planned_arrival_time',
        'actual_arrival_time',
        'status'
    ];
    
    protected $casts = [
        'planned_arrival_time' => 'datetime',
        'actual_arrival_time' => 'datetime',
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order');
    }

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'id_route')->withDefault();
    }
}