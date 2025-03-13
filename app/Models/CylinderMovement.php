<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CylinderMovement extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'cylinder_movements';

    /**
     * La clé primaire associée à la table.
     *
     * @var string
     */
    protected $primaryKey = 'id_movement';

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
        'movement_date',
        'id_product_attribut',
        'movement_type',
        'source_location',
        'destination_location',
        'id_route',
        'id_order',
        'status',
        'comments'
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array
     */
    protected $casts = [
        'movement_date' => 'datetime'
    ];

    /**
     * Le produit concerné par ce mouvement.
     */
    public function productAttribut()
    {
        return $this->belongsTo(ProductAttribut::class, 'id_product_attribut', 'id_product_attribut');
    }

    /**
     * La route de livraison associée à ce mouvement, si applicable.
     */
    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'id_route', 'id_route');
    }

    /**
     * La commande associée à ce mouvement, si applicable.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }

    /**
     * Retourne la source du mouvement (dépôt ou client).
     */
    public function sourceLocation()
    {
        if (!$this->source_location) {
            return null;
        }

        if ($this->movement_type === 'return' || $this->movement_type === 'transfer') {
            return Customer::find($this->source_location);
        } else {
            return Depot::find($this->source_location);
        }
    }

    /**
     * Retourne la destination du mouvement (dépôt ou client).
     */
    public function destinationLocation()
    {
        if (!$this->destination_location) {
            return null;
        }

        if ($this->movement_type === 'delivery') {
            return Customer::find($this->destination_location);
        } else {
            return Depot::find($this->destination_location);
        }
    }

    /**
     * Scope pour filtrer les mouvements par code produit.
     */
    public function scopeByProductCode($query, $code)
    {
        return $query->whereHas('productAttribut.product', function ($q) use ($code) {
            $q->where('procode', $code);
        });
    }
    
    /**
     * Scope pour filtrer les mouvements par libellé commercial du produit.
     */
    public function scopeByProductName($query, $name)
    {
        return $query->whereHas('productAttribut.product', function ($q) use ($name) {
            $q->where('prolibcommercial', 'like', "%{$name}%");
        });
    }

    /**
     * Scope pour filtrer les mouvements par type de produit.
     */
    public function scopeByProductType($query, $type)
    {
        return $query->whereHas('productAttribut.product', function ($q) use ($type) {
            $q->where('protype', $type);
        });
    }

    /**
     * Scope pour filtrer les mouvements par numéro de série.
     */
    public function scopeBySerialNumber($query, $serialNumber)
    {
        return $query->whereHas('productAttribut', function($q) use ($serialNumber) {
            $q->where('serial_number', 'like', "%{$serialNumber}%");
        });
    }

    /**
     * Scope pour filtrer les mouvements par période.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    /**
     * Vérifie si le mouvement est terminé.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Vérifie si le mouvement est en cours.
     */
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    /**
     * Vérifie si le mouvement est annulé.
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
}