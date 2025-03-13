<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'inventory';

    /**
     * La clé primaire associée à la table.
     *
     * @var string
     */
    protected $primaryKey = 'id_inventory';

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
        'id_depot',
        'id_product',
        'quantity',
        'last_updated',
        'last_updated_by'
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array
     */
    protected $casts = [
        'last_updated' => 'datetime'
    ];

    /**
     * Le dépôt auquel appartient cet inventaire.
     */
    public function depot()
    {
        return $this->belongsTo(Depot::class, 'id_depot', 'id_depot');
    }

    /**
     * Le produit associé à cet inventaire.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_product');
    }

    /**
     * L'employé qui a mis à jour cet inventaire en dernier.
     */
    public function lastUpdatedBy()
    {
        return $this->belongsTo(Employee::class, 'last_updated_by', 'id_employee');
    }
}
