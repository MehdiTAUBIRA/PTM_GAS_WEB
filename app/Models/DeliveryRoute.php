<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'delivery_routes';

    /**
     * La clé primaire associée à la table.
     *
     * @var string
     */
    protected $primaryKey = 'id_route';

    /**
     * Indique si les champs created_at et updated_at sont automatiquement gérés.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'id_route',
        'route_date',
        'id_vehicle',
        'id_driver',
        'start_time',
        'end_time',
        'status',
        'route_number',
        'route_name'
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array
     */
    protected $casts = [
        'route_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    /**
     * Le véhicule associé à cette route.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'id_vehicle', 'id_vehicle');
    }

    /**
     * Le chauffeur associé à cette route.
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_driver', 'id_driver');
    }

    /**
     * Les mouvements de bouteilles associés à cette route.
     */
    public function cylinderMovements()
    {
        return $this->hasMany(CylinderMovement::class, 'id_route', 'id_route');
    }

    /**
     * Les commandes associées à cette route.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'id_route', 'id_route');
    }

    /**
     * Détermine si la route est en cours.
     */
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    /**
     * Détermine si la route est terminée.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Détermine si la route est annulée.
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Détermine si la route est planifiée.
     */
    public function isPlanned()
    {
        return $this->status === 'planned';
    }

    /**
     * Obtient la durée de la route en minutes.
     */
    public function getDurationInMinutes()
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }
        return null;
    }

    /**
     * Obtient la durée formatée de la route (HH:MM).
     */
    public function getFormattedDuration()
    {
        $minutes = $this->getDurationInMinutes();
        
        if ($minutes === null) {
            return '-';
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return sprintf('%02d:%02d', $hours, $remainingMinutes);
    }

    public function stops()
    {
        return $this->hasMany(RouteStop::class, 'id_route');
    }
}