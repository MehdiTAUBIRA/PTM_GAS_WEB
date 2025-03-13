<?php

namespace App\Http\Controllers;

use App\Models\CylinderMaintenance;
use App\Models\ProductAttribut;
use App\Models\CylinderMovement;
use App\Models\Product;
use App\Models\Depot;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CylinderMaintenanceController extends Controller
{
    /**
     * Affiche la liste des maintenances de bouteilles
     */
    public function index(Request $request)
    {
        $maintenances = CylinderMaintenance::query()
            ->with(['cylinder.product'])
            ->when($request->maintenance_type, function($query, $type) {
                $query->where('maintenance_type', $type);
            })
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->serial_number, function($query, $serialNumber) {
                $query->whereHas('cylinder', function($query) use ($serialNumber) {
                    $query->where('serial_number', 'like', "%{$serialNumber}%");
                });
            })
            ->when($request->id_product, function($query, $productId) {
                $query->whereHas('cylinder', function($query) use ($productId) {
                    $query->where('id_product', $productId);
                });
            })
            ->when($request->maintenance_result, function($query, $result) {
                $query->where('maintenance_result', $result);
            })
            ->when($request->start_date && $request->end_date, function($query) use ($request) {
                if ($request->date_type === 'planned') {
                    $query->whereBetween('planned_date', [
                        Carbon::parse($request->start_date)->startOfDay(),
                        Carbon::parse($request->end_date)->endOfDay()
                    ]);
                } else {
                    $query->whereBetween('actual_date', [
                        Carbon::parse($request->start_date)->startOfDay(),
                        Carbon::parse($request->end_date)->endOfDay()
                    ]);
                }
            })
            ->when($request->overdue === 'true', function($query) {
                $query->where('status', 'planned')
                      ->where('planned_date', '<', Carbon::now());
            })
            ->orderBy($request->sort_by ?? 'planned_date', $request->sort_order ?? 'asc')
            ->paginate(15)
            ->withQueryString();

        $products = Product::where('product_type', 'cylinder')->get();

        return Inertia::render('Cylinders/Maintenance/Index', [
            'maintenances' => $maintenances,
            'products' => $products,
            'filters' => [
                'maintenance_type' => $request->maintenance_type,
                'status' => $request->status,
                'serial_number' => $request->serial_number,
                'id_product' => $request->id_product,
                'maintenance_result' => $request->maintenance_result,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'date_type' => $request->date_type,
                'overdue' => $request->overdue,
                'sort_by' => $request->sort_by,
                'sort_order' => $request->sort_order
            ],
            'maintenanceTypes' => [
                'inspection' => 'Inspection',
                'test' => 'Test',
                'repair' => 'Réparation',
                'certification' => 'Certification'
            ],
            'statuses' => [
                'planned' => 'Planifiée',
                'in_progress' => 'En cours',
                'completed' => 'Terminée',
                'cancelled' => 'Annulée'
            ],
            'maintenanceResults' => [
                'passed' => 'Réussi',
                'failed' => 'Échoué',
                'needs_repair' => 'Nécessite réparation'
            ]
        ]);
    }

    /**
     * Affiche le formulaire de création d'une maintenance
     */
    public function create()
    {
        $cylinders = ProductAttribut::whereHas('product', function($query) {
            $query->where('product_type', 'cylinder');
        })->with(['product'])->get();
        
        $depots = Depot::all();

        return Inertia::render('Cylinders/Maintenance/Create', [
            'cylinders' => $cylinders,
            'depots' => $depots,
            'maintenanceTypes' => [
                'inspection' => 'Inspection',
                'test' => 'Test',
                'repair' => 'Réparation',
                'certification' => 'Certification'
            ],
            'statuses' => [
                'planned' => 'Planifiée',
                'in_progress' => 'En cours'
            ]
        ]);
    }

    /**
     * Enregistre une nouvelle maintenance
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_product_attribut' => 'required|exists:product_attribut,id_product_attribut',
            'maintenance_type' => 'required|string|max:50',
            'planned_date' => 'required|date',
            'status' => 'required|string|max:20',
            'comments' => 'nullable|string'
        ]);

        $maintenance = CylinderMaintenance::create($validated);
        
        // Si la maintenance est "en cours", créer un mouvement et mettre à jour l'état de la bouteille
        if ($validated['status'] === 'in_progress') {
            // Récupérer un dépôt de maintenance ou le premier dépôt
            $maintenanceDepot = Depot::where('depot_type', 'maintenance')->first() ?? Depot::first();
            
            if ($maintenanceDepot) {
                // Créer un mouvement vers le dépôt de maintenance
                CylinderMovement::create([
                    'movement_date' => Carbon::now(),
                    'id_product_attribut' => $validated['id_product_attribut'],
                    'movement_type' => 'maintenance',
                    'destination_location' => $maintenanceDepot->id_depot,
                    'status' => 'completed',
                    'comments' => 'Mouvement automatique pour maintenance ' . $validated['maintenance_type']
                ]);
                
                // Mettre à jour l'état de la bouteille
                ProductAttribut::where('id_product_attribut', $validated['id_product_attribut'])
                    ->update([
                        'status' => 'maintenance',
                        'location_type' => 'maintenance',
                        'location_id' => $maintenanceDepot->id_depot
                    ]);
            }
        }

        return redirect()->route('cylinder-maintenance.index')
            ->with('message', 'Maintenance créée avec succès.');
    }

    /**
     * Affiche les détails d'une maintenance
     */
    public function show(CylinderMaintenance $maintenance)
    {
        $maintenance->load(['cylinder.product']);

        return Inertia::render('Cylinders/Maintenance/Show', [
            'maintenance' => $maintenance,
            'maintenanceTypes' => [
                'inspection' => 'Inspection',
                'test' => 'Test',
                'repair' => 'Réparation',
                'certification' => 'Certification'
            ],
            'statuses' => [
                'planned' => 'Planifiée',
                'in_progress' => 'En cours',
                'completed' => 'Terminée',
                'cancelled' => 'Annulée'
            ],
            'maintenanceResults' => [
                'passed' => 'Réussi',
                'failed' => 'Échoué',
                'needs_repair' => 'Nécessite réparation'
            ]
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'une maintenance
     */
    public function edit(CylinderMaintenance $maintenance)
    {
        $maintenance->load(['cylinder.product']);
        
        $cylinders = ProductAttribut::whereHas('product', function($query) {
            $query->where('product_type', 'cylinder');
        })->with(['product'])->get();
        
        $depots = Depot::all();

        return Inertia::render('Cylinders/Maintenance/Edit', [
            'maintenance' => $maintenance,
            'cylinders' => $cylinders,
            'depots' => $depots,
            'maintenanceTypes' => [
                'inspection' => 'Inspection',
                'test' => 'Test',
                'repair' => 'Réparation',
                'certification' => 'Certification'
            ],
            'statuses' => [
                'planned' => 'Planifiée',
                'in_progress' => 'En cours',
                'completed' => 'Terminée',
                'cancelled' => 'Annulée'
            ],
            'maintenanceResults' => [
                'passed' => 'Réussi',
                'failed' => 'Échoué',
                'needs_repair' => 'Nécessite réparation'
            ]
        ]);
    }

    /**
     * Met à jour une maintenance
     */
    public function update(Request $request, CylinderMaintenance $maintenance)
    {
        $previousStatus = $maintenance->status;
        
        $validated = $request->validate([
            'id_product_attribut' => 'required|exists:product_attribut,id_product_attribut',
            'maintenance_type' => 'required|string|max:50',
            'planned_date' => 'required|date',
            'actual_date' => 'nullable|date',
            'status' => 'required|string|max:20',
            'maintenance_result' => 'nullable|string|max:20',
            'performed_by' => 'nullable|string|max:100',
            'maintenance_cost' => 'nullable|numeric|min:0',
            'next_maintenance_date' => 'nullable|date',
            'certificate_number' => 'nullable|string|max:50',
            'comments' => 'nullable|string'
        ]);

        $maintenance->update($validated);
        
        // Gérer les transitions d'état
        if ($previousStatus !== $validated['status']) {
            // Si passe à "en cours"
            if ($validated['status'] === 'in_progress' && $previousStatus === 'planned') {
                // Récupérer un dépôt de maintenance ou le premier dépôt
                $maintenanceDepot = Depot::where('depot_type', 'maintenance')->first() ?? Depot::first();
                
                if ($maintenanceDepot) {
                    // Créer un mouvement vers le dépôt de maintenance
                    CylinderMovement::create([
                        'movement_date' => Carbon::now(),
                        'id_product_attribut' => $validated['id_product_attribut'],
                        'movement_type' => 'maintenance',
                        'destination_location' => $maintenanceDepot->id_depot,
                        'status' => 'completed',
                        'comments' => 'Mouvement automatique pour maintenance ' . $validated['maintenance_type']
                    ]);
                    
                    // Mettre à jour l'état de la bouteille
                    ProductAttribut::where('id_product_attribut', $validated['id_product_attribut'])
                        ->update([
                            'status' => 'maintenance',
                            'location_type' => 'maintenance',
                            'location_id' => $maintenanceDepot->id_depot
                        ]);
                }
            }
            // Si passe à "terminée"
            elseif ($validated['status'] === 'completed') {
                $this->completeMaintenance($maintenance, $validated);
            }
            // Si passe à "annulée"
            elseif ($validated['status'] === 'cancelled') {
                // Si la bouteille était en maintenance, on la remet en état actif
                $cylinder = ProductAttribut::find($validated['id_product_attribut']);
                
                if ($cylinder && $cylinder->status === 'maintenance') {
                    // Trouver un dépôt par défaut
                    $defaultDepot = Depot::first();
                    
                    if ($defaultDepot) {
                        $cylinder->update([
                            'status' => 'active',
                            'location_type' => 'depot',
                            'location_id' => $defaultDepot->id_depot
                        ]);
                        
                        // Créer un mouvement de retour
                        CylinderMovement::create([
                            'movement_date' => Carbon::now(),
                            'id_product_attribut' => $cylinder->id_product_attribut,
                            'movement_type' => 'return',
                            'destination_location' => $defaultDepot->id_depot,
                            'status' => 'completed',
                            'comments' => 'Retour automatique suite à l\'annulation de la maintenance'
                        ]);
                    }
                }
            }
        }

        return redirect()->route('cylinder-maintenance.show', $maintenance)
            ->with('message', 'Maintenance mise à jour avec succès.');
    }

    /**
     * Supprime une maintenance
     */
    public function destroy(CylinderMaintenance $maintenance)
    {
        // Ne pas supprimer une maintenance en cours ou terminée
        if (in_array($maintenance->status, ['in_progress', 'completed'])) {
            return back()->with('error', 'Impossible de supprimer une maintenance en cours ou terminée.');
        }

        $maintenance->delete();

        return redirect()->route('cylinder-maintenance.index')
            ->with('message', 'Maintenance supprimée avec succès.');
    }

    /**
     * Marque une maintenance comme terminée
     */
    public function complete(Request $request, CylinderMaintenance $maintenance)
    {
        $validated = $request->validate([
            'actual_date' => 'required|date',
            'maintenance_result' => 'required|string|max:20',
            'performed_by' => 'required|string|max:100',
            'maintenance_cost' => 'nullable|numeric|min:0',
            'next_maintenance_date' => 'required|date',
            'certificate_number' => 'nullable|string|max:50',
            'comments' => 'nullable|string',
            'destination_depot' => 'required|exists:depot,id_depot'
        ]);

        // Mettre à jour la maintenance
        $maintenance->update([
            'status' => 'completed',
            'actual_date' => $validated['actual_date'],
            'maintenance_result' => $validated['maintenance_result'],
            'performed_by' => $validated['performed_by'],
            'maintenance_cost' => $validated['maintenance_cost'],
            'next_maintenance_date' => $validated['next_maintenance_date'],
            'certificate_number' => $validated['certificate_number'],
            'comments' => $validated['comments']
        ]);

        $this->completeMaintenance($maintenance, array_merge($validated, [
            'destination_location' => $validated['destination_depot']
        ]));

        return redirect()->route('cylinder-maintenance.show', $maintenance)
            ->with('message', 'Maintenance terminée avec succès.');
    }

    /**
     * Action interne pour terminer une maintenance
     */
    private function completeMaintenance($maintenance, $data)
    {
        $cylinder = ProductAttribut::find($maintenance->id_product_attribut);
        
        if ($cylinder) {
            // Définir le statut de la bouteille en fonction du résultat de la maintenance
            $cylinderStatus = 'active';
            
            if ($maintenance->maintenance_result === 'failed') {
                $cylinderStatus = 'inactive';
            } elseif ($maintenance->maintenance_result === 'needs_repair') {
                // Planifier une réparation supplémentaire
                CylinderMaintenance::create([
                    'id_product_attribut' => $cylinder->id_product_attribut,
                    'maintenance_type' => 'repair',
                    'planned_date' => Carbon::now()->addDays(1),
                    'status' => 'planned',
                    'comments' => 'Réparation requise suite à ' . $maintenance->maintenance_type
                ]);
                
                $cylinderStatus = 'inactive';
            }
            
            // Mettre à jour l'état de la bouteille
            $cylinder->update([
                'status' => $cylinderStatus,
                'location_type' => 'depot',
                'location_id' => $data['destination_location'] ?? $data['destination_depot'],
                'last_inspection_date' => $maintenance->actual_date ?? Carbon::now(),
                'next_inspection_date' => $data['next_maintenance_date']
            ]);
            
            // Créer un mouvement de retour vers le dépôt
            CylinderMovement::create([
                'movement_date' => Carbon::now(),
                'id_product_attribut' => $cylinder->id_product_attribut,
                'movement_type' => 'return',
                'destination_location' => $data['destination_location'] ?? $data['destination_depot'],
                'status' => 'completed',
                'comments' => 'Retour automatique après maintenance ' . $maintenance->maintenance_type
            ]);
        }
    }

    /**
     * Affiche l'interface de planification de maintenance
     */
    public function scheduleMaintenance()
    {
        // Récupérer toutes les bouteilles qui ont besoin d'une maintenance
        $cylinders = ProductAttribut::whereHas('product', function($query) {
            $query->where('product_type', 'cylinder');
        })
        ->where(function($query) {
            $query->where('next_inspection_date', '<=', Carbon::now()->addMonths(3))
                ->orWhereNull('next_inspection_date');
        })
        ->with(['product'])
        ->get();
        
        $products = Product::where('product_type', 'cylinder')->get();

        return Inertia::render('Cylinders/Maintenance/Schedule', [
            'cylinders' => $cylinders,
            'products' => $products,
            'maintenanceTypes' => [
                'inspection' => 'Inspection',
                'test' => 'Test',
                'certification' => 'Certification'
            ]
        ]);
    }
}