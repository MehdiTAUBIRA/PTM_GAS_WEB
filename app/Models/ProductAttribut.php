<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribut extends Model
{
    use HasFactory;
    
    protected $table = 'product_attribut';
    protected $primaryKey = 'id_product_attribut';

    protected $fillable = [
        'barcode',
        'serial_number',
        'ownership',
        'manufacture_date',
        'expiration_date',
        'valve_type',
        'id_product',
        'manufacturer',
        'last_test_date',
        'state'
    ];

    protected $casts = [
        'manufacture_date' => 'datetime',
        'expiration_date' => 'datetime',
        'last_test_date' => 'datetime'
    ];

    /**
     * Le produit associé à cet attribut.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_product');
    }

    /**
     * Les mouvements de ce produit.
     */
    public function movements()
    {
        return $this->hasMany(CylinderMovement::class, 'id_product_attribut', 'id_product_attribut');
    }

    /**
     * Les maintenances de ce produit.
     */
    public function maintenances()
    {
        return $this->hasMany(CylinderMaintenance::class, 'id_product_attribut', 'id_product_attribut');
    }

    /**
     * Obtient le dernier mouvement du produit.
     */
    public function lastMovement()
    {
        return $this->movements()->orderBy('movement_date', 'desc')->first();
    }

    /**
     * Obtient l'emplacement actuel du produit basé sur son dernier mouvement.
     */
    public function getCurrentLocation()
    {
        $lastMovement = $this->lastMovement();
        
        if (!$lastMovement || $lastMovement->status !== 'completed') {
            return null;
        }
        
        $locationType = $lastMovement->movement_type === 'delivery' ? 'customer' : 
                        ($lastMovement->movement_type === 'maintenance' ? 'maintenance' : 'depot');
        
        $locationId = $lastMovement->destination_location;
        
        return [
            'type' => $locationType,
            'id' => $locationId
        ];
    }

    /**
     * Détermine si le produit est chez un client.
     */
    public function isAtCustomer()
    {
        $location = $this->getCurrentLocation();
        return $location && $location['type'] === 'customer';
    }

    /**
     * Détermine si le produit est dans un dépôt.
     */
    public function isAtDepot()
    {
        $location = $this->getCurrentLocation();
        return $location && $location['type'] === 'depot';
    }

    /**
     * Détermine si le produit est en maintenance.
     */
    public function isInMaintenance()
    {
        $location = $this->getCurrentLocation();
        return $location && $location['type'] === 'maintenance';
    }

    /**
     * Détermine si le produit est en transit.
     */
    public function isInTransit()
    {
        $lastMovement = $this->lastMovement();
        return $lastMovement && $lastMovement->status === 'in_progress';
    }

    /**
     * Retourne le client si le produit est chez un client.
     */
    public function customer()
    {
        $location = $this->getCurrentLocation();
        if ($location && $location['type'] === 'customer') {
            return Customer::find($location['id']);
        }
        return null;
    }

    /**
     * Retourne le dépôt si le produit est dans un dépôt.
     */
    public function depot()
    {
        $location = $this->getCurrentLocation();
        if ($location && $location['type'] === 'depot') {
            return Depot::find($location['id']);
        }
        return null;
    }

    /**
     * Retourne la prochaine maintenance prévue.
     */
    public function nextMaintenance()
    {
        return $this->maintenances()
            ->where('status', 'planned')
            ->orderBy('planned_date')
            ->first();
    }

    /**
     * Vérifie si la maintenance est en retard.
     */
    public function isMaintenanceOverdue()
    {
        if (!$this->last_test_date) {
            return true;
        }
        
        // Si la dernière date de test est plus ancienne que 1 an
        return $this->last_test_date->addYear()->isPast();
    }

    /**
     * Vérifie si le produit est expiré.
     */
    public function isExpired()
    {
        return $this->expiration_date && $this->expiration_date->isPast();
    }
}