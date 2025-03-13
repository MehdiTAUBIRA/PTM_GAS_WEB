<?php

namespace App\Http\Controllers;

use App\Models\CylinderMovement;
use App\Models\CylinderMaintenance;
use App\Models\ProductAttribut;
use App\Models\Depot;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductTrackingController extends Controller
{
    public function index(Request $request)
    {
        // Récupération des filtres éventuels
        $filters = $request->only(['search', 'movement_type', 'status', 'date_from', 'date_to']);
        
        // Définir les types de mouvements et statuts pour les dropdowns
        $movementTypes = [
            'delivery' => 'Livraison',
            'return' => 'Retour',
            'transfer' => 'Transfert',
            'maintenance' => 'Maintenance'
        ];
        
        $statuses = [
            'pending' => 'En attente',
            'completed' => 'Complété',
            'failed' => 'Échoué'
        ];
        
        $maintenanceTypes = [
            'inspection' => 'Inspection',
            'test' => 'Test',
            'repair' => 'Réparation',
            'certification' => 'Certification'
        ];
        
        $maintenanceStatuses = [
            'planned' => 'Planifié',
            'in_progress' => 'En cours',
            'completed' => 'Complété',
            'cancelled' => 'Annulé'
        ];
        
        // Récupération des mouvements avec pagination
        $movements = CylinderMovement::with(['productAttribute.product'])
            ->when(isset($filters['search']), function($query) use ($filters) {
                $query->whereHas('productAttribute', function($q) use ($filters) {
                    $q->where('barcode', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('serial_number', 'like', '%' . $filters['search'] . '%');
                });
            })
            ->when(isset($filters['movement_type']), function($query) use ($filters) {
                $query->where('movement_type', $filters['movement_type']);
            })
            ->when(isset($filters['status']), function($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['date_from']), function($query) use ($filters) {
                $query->whereDate('movement_date', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function($query) use ($filters) {
                $query->whereDate('movement_date', '<=', $filters['date_to']);
            })
            ->orderBy('movement_date', 'desc')
            ->paginate(10)
            ->withQueryString();
            
        // Enrichir les mouvements avec les noms des emplacements
        $movements = $this->enrichMovementsWithLocationNames($movements);
            
        // Récupération des maintenances avec pagination
        $maintenances = CylinderMaintenance::with(['productAttribute.product'])
            ->when(isset($filters['search']), function($query) use ($filters) {
                $query->whereHas('productAttribute', function($q) use ($filters) {
                    $q->where('barcode', 'like', '%' . $filters['search'] . '%')
                      ->orWhere('serial_number', 'like', '%' . $filters['search'] . '%');
                });
            })
            ->when(isset($filters['status']), function($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['date_from']), function($query) use ($filters) {
                $query->whereDate('planned_date', '>=', $filters['date_from']);
            })
            ->when(isset($filters['date_to']), function($query) use ($filters) {
                $query->whereDate('planned_date', '<=', $filters['date_to']);
            })
            ->orderBy('planned_date', 'desc')
            ->paginate(10)
            ->withQueryString();
            
        return Inertia::render('Products/Tracking', [
            'movements' => $movements,
            'maintenances' => $maintenances,
            'filters' => $filters,
            'movementTypes' => $movementTypes,
            'statuses' => $statuses,
            'maintenanceTypes' => $maintenanceTypes,
            'maintenanceStatuses' => $maintenanceStatuses
        ]);
    }
    
    public function productHistory($id)
    {
        $product = ProductAttribut::with('product')->findOrFail($id);
        
        $movements = CylinderMovement::with(['route', 'order'])
            ->where('id_product_attribut', $id)
            ->orderBy('movement_date', 'desc')
            ->get();
            
        // Enrichir les mouvements avec les noms des emplacements
        $movements = $this->enrichMovementsWithLocationDetails($movements);
            
        $maintenances = CylinderMaintenance::where('id_product_attribut', $id)
            ->orderBy('planned_date', 'desc')
            ->get();
            
        // Définir les types de mouvements et statuts pour le rendu côté client
        $movementTypes = [
            'delivery' => 'Livraison',
            'return' => 'Retour',
            'transfer' => 'Transfert',
            'maintenance' => 'Maintenance'
        ];
        
        $statuses = [
            'pending' => 'En attente',
            'completed' => 'Complété',
            'failed' => 'Échoué'
        ];
        
        $maintenanceTypes = [
            'inspection' => 'Inspection',
            'test' => 'Test',
            'repair' => 'Réparation',
            'certification' => 'Certification'
        ];
        
        $maintenanceStatuses = [
            'planned' => 'Planifié',
            'in_progress' => 'En cours',
            'completed' => 'Complété',
            'cancelled' => 'Annulé'
        ];
            
        return Inertia::render('Products/History', [
            'product' => $product,
            'movements' => $movements,
            'maintenances' => $maintenances,
            'movementTypes' => $movementTypes,
            'statuses' => $statuses,
            'maintenanceTypes' => $maintenanceTypes,
            'maintenanceStatuses' => $maintenanceStatuses
        ]);
    }
    
    /**
     * Enrichir la collection de mouvements avec les noms des emplacements source et destination
     */
    private function enrichMovementsWithLocationNames($movements)
    {
        $movementsData = $movements->toArray();
        $movementsData['data'] = collect($movementsData['data'])->map(function ($movement) {
            $movement['source_name'] = $this->getLocationName($movement['source_location'], $movement['movement_type'] === 'delivery' ? 'depot' : 'customer');
            $movement['destination_name'] = $this->getLocationName($movement['destination_location'], $movement['movement_type'] === 'delivery' ? 'customer' : 'depot');
            return $movement;
        })->toArray();
        
        return $movementsData;
    }
    
    /**
     * Enrichir la collection de mouvements avec les détails complets des emplacements
     */
    private function enrichMovementsWithLocationDetails($movements)
    {
        return $movements->map(function ($movement) {
            $movement->source_details = $this->getLocationDetails($movement->source_location, $movement->movement_type === 'delivery' ? 'depot' : 'customer');
            $movement->destination_details = $this->getLocationDetails($movement->destination_location, $movement->movement_type === 'delivery' ? 'customer' : 'depot');
            return $movement;
        });
    }
    
    /**
     * Récupérer le nom d'un emplacement en fonction de son ID et de son type
     */
    private function getLocationName($id, $type = null)
    {
        if (!$id) return '-';
        
        if ($type === 'depot' || $type === null) {
            $depot = Depot::find($id);
            if ($depot) {
                return $depot->depotlabel;
            }
        }
        
        if ($type === 'customer' || $type === null) {
            $customer = Customer::find($id);
            if ($customer) {
                return $customer->customer_name . ' ' . $customer->customer_firstname;
            }
        }
        
        return "Emplacement $id";
    }
    
    /**
     * Récupérer les détails complets d'un emplacement
     */
    private function getLocationDetails($id, $type = null)
    {
        if (!$id) return null;
        
        if ($type === 'depot' || $type === null) {
            $depot = Depot::find($id);
            if ($depot) {
                return [
                    'id' => $depot->id_depot,
                    'name' => $depot->depotlabel,
                    'address' => $depot->depotaddress,
                    'city' => $depot->depotcity,
                    'postalcode' => $depot->depotpostalcode,
                    'type' => 'Dépôt'
                ];
            }
        }
        
        if ($type === 'customer' || $type === null) {
            $customer = Customer::find($id);
            if ($customer) {
                // Récupérer l'adresse principale du client
                $address = $customer->addresses()->where('address_type', 'principal')->first();
                
                return [
                    'id' => $customer->id_customer,
                    'name' => $customer->customer_name . ' ' . $customer->customer_firstname,
                    'address' => $address ? $address->address : '',
                    'city' => '',
                    'postalcode' => $address ? $address->postalcode : '',
                    'type' => 'Client'
                ];
            }
        }
        
        return null;
    }
}