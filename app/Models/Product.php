<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'product';

    /**
     * La clé primaire associée à la table.
     *
     * @var string
     */
    protected $primaryKey = 'id_product';

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
        'procode',
        'protype',
        'unitcode',
        'proprice',
        'prolibcommercial',
        'pro_real_capacity'
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array
     */
    protected $casts = [
        'proprice' => 'float',
        'pro_real_capacity' => 'decimal:10'
    ];

    /**
     * Les attributs (instances) associés à ce produit.
     */
    public function attributes()
    {
        return $this->hasMany(ProductAttribut::class, 'id_product', 'id_product');
    }

    /**
     * Les détails de commande pour ce produit.
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'id_product', 'id_product');
    }

    /**
     * L'inventaire pour ce produit.
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'id_product', 'id_product');
    }

    /**
     * Les taux de dépôt pour ce produit.
     */
    public function depositRates()
    {
        return $this->hasMany(DepositRate::class, 'id_product', 'id_product');
    }

    /**
     * Les dépôts clients pour ce produit.
     */
    public function customerDeposits()
    {
        return $this->hasMany(CustomerDeposit::class, 'id_product', 'id_product');
    }

    /**
     * Détermine si ce produit est une bouteille.
     */
    public function isCylinder()
    {
        // Selon votre schéma, protype=1 pourrait représenter les cylindres/bouteilles
        return $this->protype == 1; 
    }

    /**
     * Compte le nombre d'instances actives pour ce produit.
     */
    public function activeInstancesCount()
    {
        return $this->attributes()
            ->where('state', 'A') // Supposant que 'A' signifie actif dans votre schéma
            ->count();
    }

    /**
     * Obtient le taux de dépôt actif pour ce produit.
     */
    public function getActiveDepositRate()
    {
        return $this->depositRates()
            ->where('is_active', true)
            ->orderBy('valid_from', 'desc')
            ->first();
    }

    /**
     * Obtient l'inventaire total du produit dans tous les dépôts.
     */
    public function getTotalInventory()
    {
        return $this->inventories()->sum('quantity');
    }

    /**
     * Obtient l'inventaire du produit par dépôt.
     */
    public function getInventoryByDepot()
    {
        return $this->inventories()
            ->with(['depot'])
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->depot->depotlabel => $item->quantity];
            });
    }
}