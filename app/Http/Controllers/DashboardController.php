<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\ProductAttribut;
use App\Models\DeliveryRoute;
use App\Models\CylinderMaintenance;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Dates de référence
        $today = Carbon::today();
        $startOfMonth = Carbon::today()->startOfMonth();
        $endOfMonth = Carbon::today()->endOfMonth();
        $startOfYear = Carbon::today()->startOfYear();
        $endOfYear = Carbon::today()->endOfYear();

        // 1. Nombre de produits en stock
        $productsInStock = Inventory::sum('quantity');

        // 2. Nombre de produits chez les clients (bouteilles livrées et non retournées)
        $productsWithCustomers = ProductAttribut::whereHas('movements', function($query) {
            $query->where('movement_type', 'delivery')
                ->whereNotIn('id_product_attribut', function($subquery) {
                    $subquery->select('id_product_attribut')
                        ->from('cylinder_movements')
                        ->where('movement_type', 'return');
                });
        })->count();

        // 3. Nombre de produits défectueux
        $defectiveProducts = ProductAttribut::where('state', 'D')->count();

        // 4. Nombre de produits en test/maintenance
        $productsInMaintenance = CylinderMaintenance::where('status', 'in_progress')
            ->orWhere(function($query) {
                $query->where('status', 'planned')
                    ->whereDate('planned_date', '<=', Carbon::today());
            })->count();

        // 5. Nombre de commandes du jour
        $ordersToday = Order::whereDate('order_date', $today)->count();

        // 6. Nombre de commandes dans le mois
        $ordersThisMonth = Order::whereBetween('order_date', [$startOfMonth, $endOfMonth])->count();

        // 7. Nombre de livraisons prévues aujourd'hui
        $deliveriesToday = DeliveryRoute::whereDate('route_date', $today)
            ->where('status', '<>', 'cancelled')
            ->count();

        // 8. Évolution des commandes au cours de l'année
        $monthlyOrders = Order::select(
                DB::raw('EXTRACT(MONTH FROM order_date) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->whereYear('order_date', $today->year)
            ->groupBy(DB::raw('EXTRACT(MONTH FROM order_date)'))
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(function ($item) {
                return $item->count;
            })
            ->toArray();

        // Assurer que tous les mois sont représentés (même ceux sans commandes)
        $orderChartData = [];
        for ($month = 1; $month <= 12; $month++) {
            $orderChartData[] = [
                'month' => Carbon::create(null, $month, 1)->format('F'),
                'count' => $monthlyOrders[$month] ?? 0
            ];
        }

        // Informations supplémentaires utiles
        $topSellingProducts = DB::table('order_details')
            ->join('product', 'order_details.id_product', '=', 'product.id_product')
            ->select(
                'product.id_product',
                'product.prolibcommercial',
                DB::raw('SUM(order_details.quantity) as total_quantity')
            )
            ->whereBetween('order_details.created_at', [$startOfYear, $endOfYear])
            ->groupBy('product.id_product', 'product.prolibcommercial')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        // Renvoyer les données à la vue
        return Inertia::render('Dashboard', [
            'stats' => [
                'productsInStock' => $productsInStock,
                'productsWithCustomers' => $productsWithCustomers,
                'defectiveProducts' => $defectiveProducts,
                'productsInMaintenance' => $productsInMaintenance,
                'ordersToday' => $ordersToday,
                'ordersThisMonth' => $ordersThisMonth,
                'deliveriesToday' => $deliveriesToday,
            ],
            'orderChartData' => $orderChartData,
            'topSellingProducts' => $topSellingProducts
        ]);
    }
}