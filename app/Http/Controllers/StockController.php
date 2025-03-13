<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\StockAlert;
use App\Models\ProductAttribut;
use App\Models\CylinderMovement;
use App\Models\CylinderMaintenance;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function overview()
    {
        // Récupérer le stock total par produit
        $stockByProduct = Inventory::join('product', 'inventory.id_product', '=', 'product.id_product')
            ->select(
                'product.id_product',
                'product.prolibcommercial',
                'product.procode',
                DB::raw('SUM(inventory.quantity) as total_quantity')
            )
            ->groupBy('product.id_product', 'product.prolibcommercial', 'product.procode')
            ->orderBy('product.prolibcommercial')
            ->get();
        
        // Récupérer les alertes de stock
        $stockAlerts = StockAlert::join('product', 'stock_alerts.id_product', '=', 'product.id_product')
            ->where('stock_alerts.is_active', true)
            ->get();
        
        // Créer un tableau associatif des seuils d'alerte par produit
        $alertThresholds = [];
        foreach ($stockAlerts as $alert) {
            $alertThresholds[$alert->id_product] = $alert->threshold_quantity;
        }
        
        // Ajouter l'état d'alerte à chaque produit
        foreach ($stockByProduct as $stock) {
            $threshold = $alertThresholds[$stock->id_product] ?? null;
            $stock->has_alert = $threshold !== null;
            $stock->alert_threshold = $threshold;
            $stock->is_below_threshold = $threshold !== null && $stock->total_quantity <= $threshold;
        }
        
        // Récupérer les statistiques générales
        $totalProducts = Product::count();
        $totalStock = Inventory::sum('quantity');
        $lowStockCount = $stockByProduct->filter(function ($stock) {
            return $stock->is_below_threshold;
        })->count();
        
        // Produits endommagés ou en maintenance
        $damagedProducts = ProductAttribut::where('state', 'D')
            ->orWhere('state', 'R')
            ->count();
        
        return Inertia::render('Stocks/Overview', [
            'stockByProduct' => $stockByProduct,
            'stats' => [
                'totalProducts' => $totalProducts,
                'totalStock' => $totalStock,
                'lowStockCount' => $lowStockCount,
                'damagedProducts' => $damagedProducts
            ]
        ]);
    }
    
    public function byDepot(Request $request)
    {
        $depotId = $request->input('depot_id');
        
        // Récupérer tous les dépôts
        $depots = Depot::orderBy('depotlabel')->get();
        
        // Si un dépôt est sélectionné, récupérer son stock
        $stockByProduct = null;
        $selectedDepot = null;
        
        if ($depotId) {
            $selectedDepot = Depot::findOrFail($depotId);
            
            $stockByProduct = Inventory::join('product', 'inventory.id_product', '=', 'product.id_product')
                ->where('inventory.id_depot', $depotId)
                ->select(
                    'product.id_product',
                    'product.prolibcommercial',
                    'product.procode',
                    'inventory.quantity',
                    'inventory.last_updated'
                )
                ->orderBy('product.prolibcommercial')
                ->get();
            
            // Récupérer les alertes de stock
            $stockAlerts = StockAlert::join('product', 'stock_alerts.id_product', '=', 'product.id_product')
                ->where('stock_alerts.is_active', true)
                ->get();
            
            // Créer un tableau associatif des seuils d'alerte par produit
            $alertThresholds = [];
            foreach ($stockAlerts as $alert) {
                $alertThresholds[$alert->id_product] = $alert->threshold_quantity;
            }
            
            // Ajouter l'état d'alerte à chaque produit
            foreach ($stockByProduct as $stock) {
                $threshold = $alertThresholds[$stock->id_product] ?? null;
                $stock->has_alert = $threshold !== null;
                $stock->alert_threshold = $threshold;
                $stock->is_below_threshold = $threshold !== null && $stock->quantity <= $threshold;
            }
        }
        
        return Inertia::render('Stocks/ByDepot', [
            'depots' => $depots,
            'selectedDepot' => $selectedDepot,
            'stockByProduct' => $stockByProduct,
        ]);
    }
    
    public function alerts(Request $request)
    {
        // Récupérer toutes les alertes
        $alerts = StockAlert::join('product', 'stock_alerts.id_product', '=', 'product.id_product')
            ->select(
                'stock_alerts.*',
                'product.prolibcommercial',
                'product.procode'
            )
            ->orderBy('stock_alerts.is_active', 'desc')
            ->orderBy('product.prolibcommercial')
            ->get();
        
        // Pour chaque alerte, récupérer le stock actuel total
        foreach ($alerts as $alert) {
            $currentStock = Inventory::where('id_product', $alert->id_product)
                ->sum('quantity');
            
            $alert->current_stock = $currentStock;
            $alert->is_below_threshold = $currentStock <= $alert->threshold_quantity;
        }
        
        // Liste des produits pour le formulaire d'ajout
        $products = Product::orderBy('prolibcommercial')->get();
        
        return Inertia::render('Stocks/Alerts', [
            'alerts' => $alerts,
            'products' => $products
        ]);
    }
    
    public function storeAlert(Request $request)
    {
        $validated = $request->validate([
            'id_product' => 'required|exists:product,id_product',
            'threshold_quantity' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);
        
        // Vérifier si une alerte existe déjà pour ce produit
        $existingAlert = StockAlert::where('id_product', $validated['id_product'])->first();
        
        if ($existingAlert) {
            // Mettre à jour l'alerte existante
            $existingAlert->update([
                'threshold_quantity' => $validated['threshold_quantity'],
                'is_active' => $validated['is_active'] ?? true
            ]);
        } else {
            // Créer une nouvelle alerte
            StockAlert::create([
                'id_product' => $validated['id_product'],
                'threshold_quantity' => $validated['threshold_quantity'],
                'is_active' => $validated['is_active'] ?? true
            ]);
        }
        
        return redirect()->route('stocks.alerts');
    }
    
    public function updateAlert(Request $request, $id)
    {
        $validated = $request->validate([
            'threshold_quantity' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);
        
        $alert = StockAlert::findOrFail($id);
        $alert->update($validated);
        
        return redirect()->route('stocks.alerts');
    }
    
    public function deleteAlert($id)
    {
        $alert = StockAlert::findOrFail($id);
        $alert->delete();
        
        return redirect()->route('stocks.alerts');
    }
    
    public function damaged()
    {
        // Récupérer tous les produits endommagés ou en réparation
        $damagedProducts = ProductAttribut::join('product', 'product_attribut.id_product', '=', 'product.id_product')
            ->whereIn('product_attribut.state', ['D', 'R']) // D = endommagé, R = en réparation
            ->select(
                'product_attribut.*',
                'product.prolibcommercial',
                'product.procode'
            )
            ->orderBy('product_attribut.state')
            ->orderBy('product.prolibcommercial')
            ->get();
            
        // Récupérer les maintenances associées
        foreach ($damagedProducts as $product) {
            $maintenance = CylinderMaintenance::where('id_product_attribut', $product->id_product_attribut)
                ->orderBy('planned_date', 'desc')
                ->first();
                
            $product->maintenance = $maintenance;
        }
        
        return Inertia::render('Stocks/Damaged', [
            'damagedProducts' => $damagedProducts
        ]);
    }
    
    public function forecast()
    {
        // Récupérer les données de vente des 6 derniers mois pour calculer les tendances
        $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();
        
        // Ventes par mois par produit
        $monthlySales = OrderDetail::join('orders', 'order_details.id_order', '=', 'orders.id_order')
            ->join('product', 'order_details.id_product', '=', 'product.id_product')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.order_date', '>=', $sixMonthsAgo)
            ->select(
                'product.id_product',
                'product.prolibcommercial',
                DB::raw("to_char(orders.order_date, 'YYYY-MM') as month"),
                DB::raw('SUM(order_details.quantity) as quantity_sold')
            )
            ->groupBy('product.id_product', 'product.prolibcommercial', 'month')
            ->orderBy('product.id_product')
            ->orderBy('month')
            ->get();
            
        // Organiser les données par produit
        $salesByProduct = [];
        foreach ($monthlySales as $sale) {
            if (!isset($salesByProduct[$sale->id_product])) {
                $salesByProduct[$sale->id_product] = [
                    'id_product' => $sale->id_product,
                    'prolibcommercial' => $sale->prolibcommercial,
                    'monthly_sales' => [],
                    'avg_monthly_sales' => 0,
                    'trend' => 0,
                    'forecast_3m' => 0
                ];
            }
            
            $salesByProduct[$sale->id_product]['monthly_sales'][$sale->month] = $sale->quantity_sold;
        }
        
        // Calculer les moyennes et tendances
        foreach ($salesByProduct as &$product) {
            // Moyenne des ventes mensuelles
            $product['avg_monthly_sales'] = count($product['monthly_sales']) > 0 
                ? array_sum($product['monthly_sales']) / count($product['monthly_sales']) 
                : 0;
                
            // Tendance (simplified)
            $months = array_keys($product['monthly_sales']);
            if (count($months) >= 2) {
                $firstMonth = $product['monthly_sales'][$months[0]];
                $lastMonth = $product['monthly_sales'][$months[count($months) - 1]];
                $product['trend'] = ($lastMonth - $firstMonth) / count($months);
            }
            
            // Prévision simple pour les 3 prochains mois
            $product['forecast_3m'] = $product['avg_monthly_sales'] * 3 + $product['trend'] * 3;
            
            // Stock actuel
            $currentStock = Inventory::where('id_product', $product['id_product'])
                ->sum('quantity');
                
            $product['current_stock'] = $currentStock;
            
            // Estimation du stock dans 3 mois
            $product['estimated_stock_3m'] = max(0, $currentStock - $product['forecast_3m']);
            
            // Alerte de stock
            $alert = StockAlert::where('id_product', $product['id_product'])
                ->where('is_active', true)
                ->first();
                
            $product['alert_threshold'] = $alert ? $alert->threshold_quantity : null;
            $product['will_be_below_threshold'] = $alert && $product['estimated_stock_3m'] <= $alert->threshold_quantity;
        }
        
        return Inertia::render('Stocks/Forecast', [
            'productsForecasts' => array_values($salesByProduct)
        ]);
    }
}