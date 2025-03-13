<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CylinderMaintenance extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'cylinder_maintenance';

    /**
     * La clé primaire associée à la table.
     *
     * @var string
     */
    protected $primaryKey = 'id_maintenance';

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
        'id_product_attribut',
        'maintenance_type',
        'planned_date',
        'actual_date',
        'status',
        'maintenance_result',
        'performed_by',
        'maintenance_cost',
        'next_maintenance_date',
        'certificate_number',
        'comments'
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array
     */
    protected $casts = [
        'planned_date' => 'datetime',
        'actual_date' => 'datetime',
        'next_maintenance_date' => 'datetime',
        'maintenance_cost' => 'decimal:2'
    ];

    /**
     * La bouteille concernée par cette maintenance.
     */
    public function cylinder()
    {
        return $this->belongsTo(ProductAttribut::class, 'id_product_attribut', 'id_product_attribut');
    }

    /**
     * Détermine si la maintenance est en retard.
     */
    public function isOverdue()
    {
        return $this->status === 'planned' && $this->planned_date < now();
    }

    /**
     * Détermine si la maintenance est à venir.
     */
    public function isUpcoming()
    {
        return $this->status === 'planned' && $this->planned_date > now();
    }
}