<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductReportController extends Controller
{
    public function index(Request $request)
    {
        // Récupérer les filtres
        $period = $request->input('period', 'month');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Convertir les dates
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // Périodes prédéfinies
        $periods = [
            'day' => 'Aujourd\'hui',
            'week' => 'Cette semaine',
            'month' => 'Ce mois',
            'year' => 'Cette année',
            'custom' => 'Personnalisé'
        ];
        
        // Mettre à jour les dates en fonction de la période sélectionnée
        if ($period == 'day') {
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();
        } else if ($period == 'week') {
            $start = Carbon::now()->startOfWeek()->startOfDay();
            $end = Carbon::now()->endOfWeek()->endOfDay();
        } else if ($period == 'month') {
            $start = Carbon::now()->startOfMonth()->startOfDay();
            $end = Carbon::now()->endOfMonth()->endOfDay();
        } else if ($period == 'year') {
            $start = Carbon::now()->startOfYear()->startOfDay();
            $end = Carbon::now()->endOfYear()->endOfDay();
        }
        
        // Requête pour les produits les plus vendus
        $topProducts = OrderDetail::join('orders', 'order_details.id_order', '=', 'orders.id_order')
            ->join('product', 'order_details.id_product', '=', 'product.id_product')
            ->whereBetween('orders.order_date', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'product.id_product',
                'product.prolibcommercial',
                'product.procode',
                DB::raw('SUM(order_details.quantity) as total_quantity'),
                DB::raw('SUM(order_details.quantity * order_details.unit_price) as total_amount')
            )
            ->groupBy('product.id_product', 'product.prolibcommercial', 'product.procode')
            ->orderBy('total_quantity', 'desc')
            ->get();
            
        // Calcul des totaux
        $totalSales = $topProducts->sum('total_amount');
        $totalQuantity = $topProducts->sum('total_quantity');
        
        // Données pour le graphique d'évolution des ventes
        $salesTrend = $this->getSalesTrend($start, $end, $period);
        
        return Inertia::render('Products/Report', [
            'topProducts' => $topProducts,
            'totalSales' => $totalSales,
            'totalQuantity' => $totalQuantity,
            'periods' => $periods,
            'filters' => [
                'period' => $period,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
            ],
            'salesTrend' => $salesTrend,
        ]);
    }
    
    private function getSalesTrend($start, $end, $period)
    {
        $groupFormat = '%Y-%m-%d';
        $carbonFormat = 'Y-m-d';
        
        if ($period == 'year') {
            $groupFormat = '%Y-%m';
            $carbonFormat = 'Y-m';
        }
        
        $salesData = OrderDetail::join('orders', 'order_details.id_order', '=', 'orders.id_order')
            ->whereBetween('orders.order_date', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                DB::raw("to_char(orders.order_date, '$groupFormat') as date"),
                DB::raw('SUM(order_details.quantity) as quantity'),
                DB::raw('SUM(order_details.quantity * order_details.unit_price) as amount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Préparer les données pour le graphique
        $dateRange = collect();
        $current = clone $start;
        
        while ($current <= $end) {
            $dateRange->push([
                'date' => $current->format($carbonFormat),
                'quantity' => 0,
                'amount' => 0
            ]);
            
            if ($period == 'year') {
                $current->addMonth();
            } else {
                $current->addDay();
            }
        }
        
        // Fusionner avec les données réelles
        foreach ($salesData as $sale) {
            $dateIndex = $dateRange->search(function ($item) use ($sale) {
                return $item['date'] == $sale->date;
            });
            
            if ($dateIndex !== false) {
                $dateRange[$dateIndex]['quantity'] = $sale->quantity;
                $dateRange[$dateIndex]['amount'] = $sale->amount;
            }
        }
        
        return $dateRange->values();
    }
    
    public function productDetails(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        // Récupérer les filtres
        $period = $request->input('period', 'month');
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Convertir les dates
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // Périodes prédéfinies
        $periods = [
            'day' => 'Aujourd\'hui',
            'week' => 'Cette semaine',
            'month' => 'Ce mois',
            'year' => 'Cette année',
            'custom' => 'Personnalisé'
        ];
        
        // Mettre à jour les dates en fonction de la période sélectionnée
        if ($period == 'day') {
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();
        } else if ($period == 'week') {
            $start = Carbon::now()->startOfWeek()->startOfDay();
            $end = Carbon::now()->endOfWeek()->endOfDay();
        } else if ($period == 'month') {
            $start = Carbon::now()->startOfMonth()->startOfDay();
            $end = Carbon::now()->endOfMonth()->endOfDay();
        } else if ($period == 'year') {
            $start = Carbon::now()->startOfYear()->startOfDay();
            $end = Carbon::now()->endOfYear()->endOfDay();
        }
        
        // Ventes du produit par période
        $sales = OrderDetail::join('orders', 'order_details.id_order', '=', 'orders.id_order')
            ->join('customer', 'orders.id_customer', '=', 'customer.id_customer')
            ->where('order_details.id_product', $id)
            ->whereBetween('orders.order_date', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'orders.id_order',
                'orders.order_number',
                'orders.order_date',
                'customer.customer_name',
                'customer.customer_firstname',
                'order_details.quantity',
                'order_details.unit_price',
                DB::raw('order_details.quantity * order_details.unit_price as total_amount')
            )
            ->orderBy('orders.order_date', 'desc')
            ->get();
            
        // Totaux
        $totalQuantity = $sales->sum('quantity');
        $totalAmount = $sales->sum('total_amount');
        
        // Tendance des ventes
        $salesTrend = $this->getProductSalesTrend($id, $start, $end, $period);
        
        return Inertia::render('Products/ProductReport', [
            'product' => $product,
            'sales' => $sales,
            'totalQuantity' => $totalQuantity,
            'totalAmount' => $totalAmount,
            'periods' => $periods,
            'filters' => [
                'period' => $period,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
            ],
            'salesTrend' => $salesTrend,
        ]);
    }
    
    private function getProductSalesTrend($productId, $start, $end, $period)
    {
        $groupFormat = '%Y-%m-%d';
        $carbonFormat = 'Y-m-d';
        
        if ($period == 'year') {
            $groupFormat = '%Y-%m';
            $carbonFormat = 'Y-m';
        }
        
        $salesData = OrderDetail::join('orders', 'order_details.id_order', '=', 'orders.id_order')
            ->where('order_details.id_product', $productId)
            ->whereBetween('orders.order_date', [$start, $end])
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                DB::raw("DATE_FORMAT(orders.order_date, '$groupFormat') as date"),
                DB::raw('SUM(order_details.quantity) as quantity'),
                DB::raw('SUM(order_details.quantity * order_details.unit_price) as amount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Préparer les données pour le graphique
        $dateRange = collect();
        $current = clone $start;
        
        while ($current <= $end) {
            $dateRange->push([
                'date' => $current->format($carbonFormat),
                'quantity' => 0,
                'amount' => 0
            ]);
            
            if ($period == 'year') {
                $current->addMonth();
            } else {
                $current->addDay();
            }
        }
        
        // Fusionner avec les données réelles
        foreach ($salesData as $sale) {
            $dateIndex = $dateRange->search(function ($item) use ($sale) {
                return $item['date'] == $sale->date;
            });
            
            if ($dateIndex !== false) {
                $dateRange[$dateIndex]['quantity'] = $sale->quantity;
                $dateRange[$dateIndex]['amount'] = $sale->amount;
            }
        }
        
        return $dateRange->values();
    }
}