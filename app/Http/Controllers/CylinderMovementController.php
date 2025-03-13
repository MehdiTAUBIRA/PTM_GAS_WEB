<?php

namespace App\Http\Controllers;

use App\Models\CylinderMovement;
use App\Models\ProductAttribut;
use App\Models\Product;
use App\Models\Depot;
use App\Models\Customer;
use App\Models\DeliveryRoute;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CylinderMovementController extends Controller
{
    /**
     * Affiche la liste des mouvements de cylindres
     */
    public function index(Request $request)
    {
        $movements = CylinderMovement::query()
            ->with(['productAttribut.product'])
            ->when($request->movement_type, function($query, $type) {
                $query->where('movement_type', $type);
            })
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->serial_number, function($query, $serialNumber) {
                $query->bySerialNumber($serialNumber);
            })
            ->when($request->procode, function($query, $procode) {
                $query->byProductCode($procode);
            })
            ->when($request->prolibcommercial, function($query, $name) {
                $query->byProductName($name);
            })
            ->when($request->protype, function($query, $type) {
                $query->byProductType($type);
            })
            ->when($request->start_date && $request->end_date, function($query) use ($request) {
                $query->byDateRange(
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                );
            })
            ->orderBy('movement_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        $productTypes = Product::select(DB::raw('DISTINCT protype'))
            ->pluck('protype')
            ->toArray();
            
        $productCodes = Product::select('procode', 'prolibcommercial')
            ->orderBy('procode')
            ->get();

        return Inertia::render('Cylinders/Movements/Index', [
            'movements' => $movements,
            'productTypes' => $productTypes,
            'productCodes' => $productCodes,
            'filters' => [
                'movement_type' => $request->movement_type,
                'status' => $request->status,
                'serial_number' => $request->serial_number,
                'procode' => $request->procode,
                'prolibcommercial' => $request->prolibcommercial,
                'protype' => $request->protype,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ],
            'movementTypes' => [
                'delivery' => 'Livraison',
                'return' => 'Retour',
                'transfer' => 'Transfert',
                'maintenance' => 'Maintenance',
                'acquisition' => 'Acquisition'
            ],
            'statuses' => [
                'pending' => 'En attente',
                'in_progress' => 'En cours',
                'completed' => 'Terminé',
                'cancelled' => 'Annulé'
            ]
        ]);
    }

    /**
     * Affiche le formulaire de création d'un mouvement
     */
    public function create()
    {
        $products = ProductAttribut::with(['product'])->get();
        
        $depots = Depot::all();
        $customers = Customer::all();
        $routes = DeliveryRoute::all();
        $orders = Order::orderBy('order_date', 'desc')->limit(50)->get();

        return Inertia::render('Cylinders/Movements/Create', [
            'products' => $products,
            'depots' => $depots,
            'customers' => $customers,
            'routes' => $routes,
            'orders' => $orders,
            'movementTypes' => [
                'delivery' => 'Livraison',
                'return' => 'Retour',
                'transfer' => 'Transfert',
                'maintenance' => 'Maintenance'
            ],
            'statuses' => [
                'pending' => 'En attente',
                'in_progress' => 'En cours',
                'completed' => 'Terminé'
            ]
        ]);
    }

    /**
     * Enregistre un nouveau mouvement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_product_attribut' => 'required|exists:product_attribut,id_product_attribut',
            'movement_type' => 'required|string|max:20',
            'movement_date' => 'required|date',
            'source_location' => 'nullable|integer',
            'destination_location' => 'required|integer',
            'id_route' => 'nullable|exists:delivery_routes,id_route',
            'id_order' => 'nullable|exists:orders,id_order',
            'status' => 'required|string|max:20',
            'comments' => 'nullable|string'
        ]);

        $movement = CylinderMovement::create($validated);

        return redirect()->route('cylinder-movements.index')
            ->with('message', 'Mouvement créé avec succès.');
    }

    /**
     * Affiche les détails d'un mouvement
     */
    public function show(CylinderMovement $movement)
    {
        $movement->load(['productAttribut.product', 'route', 'order']);
        
        // Obtenir la source et la destination
        $source = $movement->sourceLocation();
        $destination = $movement->destinationLocation();

        return Inertia::render('Cylinders/Movements/Show', [
            'movement' => $movement,
            'source' => $source,
            'destination' => $destination,
            'movementTypes' => [
                'delivery' => 'Livraison',
                'return' => 'Retour',
                'transfer' => 'Transfert',
                'maintenance' => 'Maintenance',
                'acquisition' => 'Acquisition'
            ],
            'statuses' => [
                'pending' => 'En attente',
                'in_progress' => 'En cours',
                'completed' => 'Terminé',
                'cancelled' => 'Annulé'
            ]
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'un mouvement
     */
    public function edit(CylinderMovement $movement)
    {
        $movement->load(['productAttribut.product']);
        
        $products = ProductAttribut::with(['product'])->get();
        $depots = Depot::all();
        $customers = Customer::all();
        $routes = DeliveryRoute::all();
        $orders = Order::orderBy('order_date', 'desc')->limit(50)->get();

        return Inertia::render('Cylinders/Movements/Edit', [
            'movement' => $movement,
            'products' => $products,
            'depots' => $depots,
            'customers' => $customers,
            'routes' => $routes,
            'orders' => $orders,
            'movementTypes' => [
                'delivery' => 'Livraison',
                'return' => 'Retour',
                'transfer' => 'Transfert',
                'maintenance' => 'Maintenance'
            ],
            'statuses' => [
                'pending' => 'En attente',
                'in_progress' => 'En cours',
                'completed' => 'Terminé',
                'cancelled' => 'Annulé'
            ]
        ]);
    }

    /**
     * Met à jour un mouvement
     */
    public function update(Request $request, CylinderMovement $movement)
    {
        $validated = $request->validate([
            'id_product_attribut' => 'required|exists:product_attribut,id_product_attribut',
            'movement_type' => 'required|string|max:20',
            'movement_date' => 'required|date',
            'source_location' => 'nullable|integer',
            'destination_location' => 'required|integer',
            'id_route' => 'nullable|exists:delivery_routes,id_route',
            'id_order' => 'nullable|exists:orders,id_order',
            'status' => 'required|string|max:20',
            'comments' => 'nullable|string'
        ]);

        $movement->update($validated);

        return redirect()->route('cylinder-movements.show', $movement)
            ->with('message', 'Mouvement mis à jour avec succès.');
    }

    /**
     * Supprime un mouvement
     */
    public function destroy(CylinderMovement $movement)
    {
        // On ne supprime pas les mouvements complétés pour conserver l'historique
        if ($movement->status === 'completed') {
            return back()->with('error', 'Impossible de supprimer un mouvement terminé.');
        }

        $movement->delete();

        return redirect()->route('cylinder-movements.index')
            ->with('message', 'Mouvement supprimé avec succès.');
    }
}