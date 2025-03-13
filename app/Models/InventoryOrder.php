<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryOrder extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'inventory_order';

    /**
     * La clé primaire associée à la table.
     *
     * @var string
     */
    protected $primaryKey = 'id_inventory_order';

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
        'order_date',
        'id_depot',
        'id_employee_creator',
        'id_employee_assigned',
        'status',
        'start_date',
        'completion_date',
        'comments'
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array
     */
    protected $casts = [
        'order_date' => 'datetime',
        'start_date' => 'datetime',
        'completion_date' => 'datetime'
    ];

    /**
     * Le dépôt concerné par cet ordre d'inventaire.
     */
    public function depot()
    {
        return $this->belongsTo(Depot::class, 'id_depot', 'id_depot');
    }

    /**
     * L'employé qui a créé cet ordre d'inventaire.
     */
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'id_employee_creator', 'id_employee');
    }

    /**
     * L'employé assigné à cet ordre d'inventaire.
     */
    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'id_employee_assigned', 'id_employee');
    }

    /**
     * Les détails de cet ordre d'inventaire.
     */
    public function details()
    {
        return $this->hasMany(InventoryOrderDetail::class, 'id_inventory_order', 'id_inventory_order');
    }
}