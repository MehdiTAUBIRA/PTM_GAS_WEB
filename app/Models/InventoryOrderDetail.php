<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryOrderDetail extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'inventory_order_detail';

    /**
     * La clé primaire associée à la table.
     *
     * @var string
     */
    protected $primaryKey = 'id_inventory_order_detail';

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
        'id_inventory_order',
        'id_product',
        'expected_quantity',
        'counted_quantity',
        'difference',
        'comments'
    ];

    /**
     * L'ordre d'inventaire parent.
     */
    public function inventoryOrder()
    {
        return $this->belongsTo(InventoryOrder::class, 'id_inventory_order', 'id_inventory_order');
    }

    /**
     * Le produit concerné par ce détail d'ordre d'inventaire.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_product');
    }
}