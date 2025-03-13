<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Depot;
use App\Models\Product;
use App\Models\InventoryOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Affiche l'état actuel de l'inventaire
     */
    public function index(Request $request)
    {
        $inventory = Inventory::query()
            ->with(['depot', 'product', 'lastUpdatedBy'])
            ->when($request->depot_id, function($query, $depotId) {
                $query->where('id_depot', $depotId);
            })
            ->when($request->product_id, function($query, $productId) {
                $query->where('id_product', $productId);
            })
            ->when($request->search, function($query, $search) {
                $query->whereHas('product', function($query) use ($search) {
                    $query->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_reference', 'like', "%{$search}%");
                })
                ->orWhereHas('depot', function($query) use ($search) {
                    $query->where('depot_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_updated', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Inventory/Index', [
            'inventory' => $inventory,
            'depots' => Depot::all(),
            'products' => Product::all(),
            'filters' => [
                'depot_id' => $request->depot_id,
                'product_id' => $request->product_id,
                'search' => $request->search
            ]
        ]);
    }

    /**
     * Affiche les rapports d'inventaire
     */
    public function report(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();
        
        // Récupérer les ordres d'inventaire terminés dans la période
        $orders = InventoryOrder::with(['depot', 'assignedEmployee', 'details.product'])
            ->where('status', 'completed')
            ->whereBetween('completion_date', [$startDate, $endDate])
            ->when($request->depot_id, function($query, $depotId) {
                $query->where('id_depot', $depotId);
            })
            ->get();
            
        // Calculer les statistiques
        $totalOrders = $orders->count();
        $totalProducts = $orders->flatMap->details->count();
        
        // Produits avec les plus grandes différences
        $productDifferences = $orders->flatMap->details
            ->where('difference', '!=', 0)
            ->groupBy('id_product')
            ->map(function($group) {
                $product = $group->first()->product;
                return [
                    'id_product' => $product->id_product,
                    'product_name' => $product->product_name,
                    'product_reference' => $product->product_reference,
                    'total_difference' => $group->sum('difference'),
                    'avg_difference' => $group->avg('difference'),
                    'count' => $group->count()
                ];
            })
            ->sortByDesc('total_difference')
            ->values()
            ->take(10);
            
        // Dépôts avec le plus de différences
        $depotDifferences = $orders
            ->groupBy('id_depot')
            ->map(function($group) {
                $depot = $group->first()->depot;
                $details = $group->flatMap->details;
                return [
                    'id_depot' => $depot->id_depot,
                    'depot_name' => $depot->depot_name,
                    'total_difference' => $details->sum('difference'),
                    'total_products' => $details->count(),
                    'count' => $group->count()
                ];
            })
            ->sortByDesc('total_difference')
            ->values();
            
        // Historique des inventaires par mois
        $inventoryHistory = InventoryOrder::where('status', 'completed')
            ->select(
                DB::raw('to_char(completion_date, \'YYYY-MM\') as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();
            
        return Inertia::render('Inventory/Report', [
            'orders' => $orders,
            'stats' => [
                'total_orders' => $totalOrders,
                'total_products' => $totalProducts,
                'product_differences' => $productDifferences,
                'depot_differences' => $depotDifferences,
                'inventory_history' => $inventoryHistory
            ],
            'depots' => Depot::all(),
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'depot_id' => $request->depot_id
            ]
        ]);
    }

    /**
     * Ajuste manuellement l'inventaire
     */
    public function adjust(Request $request)
    {
        $request->validate([
            'id_depot' => 'required|exists:depot,id_depot',
            'id_product' => 'required|exists:product,id_product',
            'quantity' => 'required|integer|min:0',
            'comments' => 'nullable|string'
        ]);

        $inventory = Inventory::firstOrNew([
            'id_depot' => $request->id_depot,
            'id_product' => $request->id_product
        ]);

        $oldQuantity = $inventory->quantity ?? 0;
        $inventory->quantity = $request->quantity;
        $inventory->last_updated = Carbon::now();
        $inventory->last_updated_by = Auth::id();
        $inventory->save();

        // Optionnellement, vous pourriez enregistrer l'ajustement dans une table d'historique

        return back()->with('message', "Inventaire ajusté avec succès. Quantité modifiée de {$oldQuantity} à {$request->quantity}.");
    }
}