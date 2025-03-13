<?php

namespace App\Http\Controllers;

use App\Models\DeliveryRoute;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Order;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\RouteStop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeliveryRouteController extends Controller
{
    public function index(Request $request)
    {
        // Filtres
        $dateFilter = $request->input('date');
        $driverFilter = $request->input('driver_id');
        $statusFilter = $request->input('status');
        
        // Obtenir les tournées avec pagination
        $routes = DeliveryRoute::with(['driver', 'vehicle'])
            ->when($dateFilter, function($query) use ($dateFilter) {
                return $query->whereDate('route_date', $dateFilter);
            })
            ->when($driverFilter, function($query) use ($driverFilter) {
                return $query->where('id_driver', $driverFilter);
            })
            ->when($statusFilter, function($query) use ($statusFilter) {
                return $query->where('status', $statusFilter);
            })
            ->orderBy('route_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(10)
            ->withQueryString();
            
        // Obtenir les chauffeurs pour le filtre
        $drivers = Driver::where('driver_status', true)
            ->orderBy('drivername')
            ->get();
            
        // Statuts disponibles
        $statuses = [
            'planned' => 'Planifiée',
            'in_progress' => 'En cours',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée'
        ];
        
        return Inertia::render('DeliveryRoutes/Index', [
            'routes' => $routes,
            'drivers' => $drivers,
            'statuses' => $statuses,
            'filters' => [
                'date' => $dateFilter,
                'driver_id' => $driverFilter,
                'status' => $statusFilter
            ]
        ]);
    }
    
    public function create()
    {
        // Obtenir les chauffeurs disponibles
        $drivers = Driver::where('driver_status', true)
            ->orderBy('drivername')
            ->get();
            
        // Obtenir les véhicules disponibles
        $vehicles = Vehicle::orderBy('vehiclename')
            ->get();
            
        // Obtenir les commandes en attente de livraison
        $pendingOrders = Order::with(['customer', 'address'])
            ->where('status', 'confirmed')
            ->whereNull('delivery_date')
            ->orWhere(function($query) {
                $query->where('status', 'in_delivery')
                    ->whereDate('delivery_date', '>=', now());
            })
            ->orderBy('order_date')
            ->get();
            
        return Inertia::render('DeliveryRoutes/Create', [
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'pendingOrders' => $pendingOrders,
            'defaultDate' => now()->format('Y-m-d')
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_date' => 'required|date',
            'id_vehicle' => 'required|exists:vehicle,id_vehicle',
            'id_driver' => 'required|exists:driver,id_driver',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'orders' => 'required|array|min:1',
            'orders.*.id_order' => 'required|exists:orders,id_order',
            'orders.*.stop_order' => 'required|integer|min:1'
        ]);
        
        // Créer la tournée
        $route = DeliveryRoute::create([
            'route_date' => $validated['route_date'],
            'id_vehicle' => $validated['id_vehicle'],
            'id_driver' => $validated['id_driver'],
            'start_time' => Carbon::parse($validated['route_date'] . ' ' . $validated['start_time']),
            'end_time' => $validated['end_time'] ? Carbon::parse($validated['route_date'] . ' ' . $validated['end_time']) : null,
            'status' => $validated['status']
        ]);
        
        // Créer les arrêts de la tournée
        foreach ($validated['orders'] as $orderData) {
            RouteStop::create([
                'id_route' => $route->id_route,
                'id_order' => $orderData['id_order'],
                'stop_order' => $orderData['stop_order'],
                'planned_arrival_time' => null, // À calculer si nécessaire
                'status' => 'pending'
            ]);
            
            // Mettre à jour le statut de la commande
            $order = Order::find($orderData['id_order']);
            if ($order && $order->status == 'confirmed') {
                $order->update([
                    'status' => 'in_delivery',
                    'delivery_date' => $validated['route_date']
                ]);
            }
        }
        
        return redirect()->route('delivery-routes.show', $route->id_route)
            ->with('success', 'Tournée créée avec succès.');
    }
    
    public function show($id)
    {
        $route = DeliveryRoute::with([
            'driver', 
            'vehicle',
            'stops.order.customer',
            'stops.order.address',
            'stops.order.details.product'
        ])->findOrFail($id);
        
        // Trier les arrêts par ordre
        $stops = $route->stops->sortBy('stop_order')->values();
        
        // Statuts disponibles pour les arrêts
        $stopStatuses = [
            'pending' => 'En attente',
            'completed' => 'Terminé',
            'failed' => 'Échoué'
        ];
        
        return Inertia::render('DeliveryRoutes/Show', [
            'route' => $route,
            'stops' => $stops,
            'stopStatuses' => $stopStatuses
        ]);
    }
    
    public function edit($id)
    {
        $route = DeliveryRoute::with(['stops.order'])->findOrFail($id);
        
        // Impossible de modifier une tournée terminée ou annulée
        if (in_array($route->status, ['completed', 'cancelled'])) {
            return redirect()->route('delivery-routes.show', $route->id_route)
                ->with('error', 'Impossible de modifier une tournée terminée ou annulée.');
        }
        
        // Obtenir les chauffeurs disponibles
        $drivers = Driver::where('driver_status', true)
            ->orderBy('drivername')
            ->get();
            
        // Obtenir les véhicules disponibles
        $vehicles = Vehicle::orderBy('vehiclename')
            ->get();
            
        // Obtenir les commandes en attente de livraison (qui ne sont pas déjà dans d'autres tournées)
        $pendingOrders = Order::with(['customer', 'address'])
            ->where(function($query) use ($route) {
                $query->where('status', 'confirmed')
                    ->whereNull('delivery_date');
            })
            ->orWhere(function($query) use ($route) {
                $query->where('status', 'in_delivery')
                    ->whereHas('routeStops', function($q) use ($route) {
                        $q->where('id_route', $route->id_route);
                    });
            })
            ->orderBy('order_date')
            ->get();
            
        // Préparer les données des arrêts
        $currentStops = $route->stops->map(function($stop) {
            return [
                'id_order' => $stop->id_order,
                'stop_order' => $stop->stop_order
            ];
        });
        
        // Formater les heures
        $startTime = $route->start_time ? Carbon::parse($route->start_time)->format('H:i') : null;
        $endTime = $route->end_time ? Carbon::parse($route->end_time)->format('H:i') : null;
        
        return Inertia::render('DeliveryRoutes/Edit', [
            'route' => array_merge($route->toArray(), [
                'start_time' => $startTime,
                'end_time' => $endTime
            ]),
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'pendingOrders' => $pendingOrders,
            'currentStops' => $currentStops
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $route = DeliveryRoute::findOrFail($id);
        
        // Impossible de modifier une tournée terminée ou annulée
        if (in_array($route->status, ['completed', 'cancelled'])) {
            return redirect()->route('delivery-routes.show', $route->id_route)
                ->with('error', 'Impossible de modifier une tournée terminée ou annulée.');
        }
        
        $validated = $request->validate([
            'route_date' => 'required|date',
            'id_vehicle' => 'required|exists:vehicle,id_vehicle',
            'id_driver' => 'required|exists:driver,id_driver',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'orders' => 'required|array|min:1',
            'orders.*.id_order' => 'required|exists:orders,id_order',
            'orders.*.stop_order' => 'required|integer|min:1'
        ]);
        
        // Mettre à jour la tournée
        $route->update([
            'route_date' => $validated['route_date'],
            'id_vehicle' => $validated['id_vehicle'],
            'id_driver' => $validated['id_driver'],
            'start_time' => Carbon::parse($validated['route_date'] . ' ' . $validated['start_time']),
            'end_time' => $validated['end_time'] ? Carbon::parse($validated['route_date'] . ' ' . $validated['end_time']) : null,
            'status' => $validated['status']
        ]);
        
        // Obtenir les ID des commandes actuelles
        $currentOrderIds = $route->stops->pluck('id_order')->toArray();
        
        // Obtenir les ID des nouvelles commandes
        $newOrderIds = collect($validated['orders'])->pluck('id_order')->toArray();
        
        // Commandes à supprimer
        $ordersToRemove = array_diff($currentOrderIds, $newOrderIds);
        
        // Supprimer les arrêts pour les commandes qui ne sont plus dans la tournée
        if (!empty($ordersToRemove)) {
            RouteStop::where('id_route', $route->id_route)
                ->whereIn('id_order', $ordersToRemove)
                ->delete();
                
            // Mettre à jour les commandes supprimées
            Order::whereIn('id_order', $ordersToRemove)
                ->where('status', 'in_delivery')
                ->update([
                    'status' => 'confirmed',
                    'delivery_date' => null
                ]);
        }
        
        // Mettre à jour ou créer les arrêts
        foreach ($validated['orders'] as $orderData) {
            $routeStop = RouteStop::updateOrCreate(
                [
                    'id_route' => $route->id_route,
                    'id_order' => $orderData['id_order']
                ],
                [
                    'stop_order' => $orderData['stop_order'],
                    'status' => 'pending'
                ]
            );
            
            // Mettre à jour le statut de la commande si nécessaire
            $order = Order::find($orderData['id_order']);
            if ($order && $order->status == 'confirmed') {
                $order->update([
                    'status' => 'in_delivery',
                    'delivery_date' => $validated['route_date']
                ]);
            }
        }
        
        // Si la tournée est marquée comme terminée, mettre à jour les commandes
        if ($validated['status'] == 'completed') {
            Order::whereIn('id_order', $newOrderIds)
                ->where('status', 'in_delivery')
                ->update([
                    'status' => 'delivered'
                ]);
        }
        
        return redirect()->route('delivery-routes.show', $route->id_route)
            ->with('success', 'Tournée mise à jour avec succès.');
    }
    
    public function updateStopStatus(Request $request, $id, $stopId)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,failed',
            'actual_arrival_time' => 'nullable|date_format:H:i'
        ]);
        
        $routeStop = RouteStop::where('id_route', $id)
            ->where('id_route_stop', $stopId)
            ->firstOrFail();
            
        $routeStop->update([
            'status' => $validated['status'],
            'actual_arrival_time' => $validated['actual_arrival_time'] 
                ? Carbon::parse($routeStop->route->route_date . ' ' . $validated['actual_arrival_time'])
                : null
        ]);
        
        // Mettre à jour le statut de la commande si nécessaire
        $order = Order::find($routeStop->id_order);
        if ($order) {
            if ($validated['status'] == 'completed') {
                $order->update(['status' => 'delivered']);
            } else if ($validated['status'] == 'failed') {
                $order->update(['status' => 'confirmed', 'delivery_date' => null]);
            }
        }
        
        return redirect()->back()->with('success', 'Statut de l\'arrêt mis à jour.');
    }
    
    public function destroy($id)
    {
        $route = DeliveryRoute::findOrFail($id);
        
        // Récupérer les commandes associées
        $orderIds = $route->stops->pluck('id_order')->toArray();
        
        // Supprimer les arrêts
        RouteStop::where('id_route', $id)->delete();
        
        // Mettre à jour les commandes
        Order::whereIn('id_order', $orderIds)
            ->where('status', 'in_delivery')
            ->update([
                'status' => 'confirmed',
                'delivery_date' => null
            ]);
            
        // Supprimer la tournée
        $route->delete();
        
        return redirect()->route('delivery-routes.index')
            ->with('success', 'Tournée supprimée avec succès.');
    }
    
    public function driverRoutes($driverId)
    {
        $driver = Driver::findOrFail($driverId);
        
        // Obtenir les tournées du jour et futures pour ce chauffeur
        $routes = DeliveryRoute::with([
            'stops.order.customer',
            'stops.order.address',
            'stops.order.details.product',
            'vehicle'
        ])
        ->where('id_driver', $driverId)
        ->where('route_date', '>=', now()->startOfDay())
        ->where('status', '!=', 'cancelled')
        ->orderBy('route_date')
        ->orderBy('start_time')
        ->get();
        
        return Inertia::render('DeliveryRoutes/DriverRoutes', [
            'driver' => $driver,
            'routes' => $routes
        ]);
    }
}