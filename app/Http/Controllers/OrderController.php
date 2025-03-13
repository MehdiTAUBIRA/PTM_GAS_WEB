<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            ->with(['customer', 'customerAddress', 'orderDetails.product'])
            ->when($request->search, function($query, $search) {
                $query->where(function($query) use ($search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function($query) use ($search) {
                            $query->where('customer_name', 'like', "%{$search}%")
                                ->orWhere('customer_firstname', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('order_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'statuses' => $this->getOrderStatuses()
        ]);
    }

    public function create()
    {
        return Inertia::render('Orders/Create', [
            'customers' => Customer::with('addresses')->get(),
            'products' => Product::all(),
            'statuses' => $this->getOrderStatuses()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_customer' => 'required|exists:customer,id_customer',
            'id_cus_address' => 'required|exists:customer_address,id_cus_address',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after:order_date',
            'status' => 'required|in:' . implode(',', array_keys($this->getOrderStatuses())),
            'comments' => 'nullable|string',
            'orderDetails' => 'required|array|min:1',
            'orderDetails.*.id_product' => 'required|exists:product,id_product',
            'orderDetails.*.quantity' => 'required|integer|min:1',
            'orderDetails.*.unit_price' => 'required|numeric|min:0'
        ]);

        // Générer un numéro de commande unique
        $validated['order_number'] = 'CMD-' . date('Ymd') . '-' . uniqid();

        // Calculer le montant total
        $total_amount = collect($validated['orderDetails'])->sum(function($detail) {
            return $detail['quantity'] * $detail['unit_price'];
        });

        // Créer la commande
        $order = Order::create([
            'order_number' => $validated['order_number'],
            'id_customer' => $validated['id_customer'],
            'id_cus_address' => $validated['id_cus_address'],
            'order_date' => $validated['order_date'],
            'delivery_date' => $validated['delivery_date'],
            'status' => $validated['status'],
            'total_amount' => $total_amount,
            'comments' => $validated['comments']
        ]);

        // Créer les détails de la commande
        foreach ($validated['orderDetails'] as $detail) {
            $order->orderDetails()->create($detail);
        }

        return redirect()->route('orders.index')
            ->with('message', 'Commande créée avec succès');
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'customerAddress', 'orderDetails.product']);
        
        return Inertia::render('Orders/Show', [
            'order' => $order,
            'statuses' => $this->getOrderStatuses()
        ]);
    }

    public function updateStatus(Order $order, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys($this->getOrderStatuses()))
        ]);

        $order->update($validated);

        return redirect()->back()
            ->with('message', 'Statut de la commande mis à jour');
    }

    private function getOrderStatuses()
    {
        return [
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'in_delivery' => 'En livraison',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée'
        ];
    }

    public function report(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $orders = Order::query()
            ->with(['customer', 'orderDetails'])
            ->whereBetween('order_date', [$startDate, $endDate])
            ->orderBy('order_date', 'desc')
            ->get();

        // Calcul des statistiques
        $stats = [
            'total_orders' => $orders->count(),
            'total_amount' => $orders->sum('total_amount'),
            'average_amount' => $orders->avg('total_amount'),
            'status_count' => $orders->groupBy('status')->map->count(),
        ];

        return Inertia::render('Orders/Report', [
            'orders' => $orders,
            'stats' => $stats,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'statuses' => $this->getOrderStatuses()
        ]);
    }
}