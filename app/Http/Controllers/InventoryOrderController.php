<?php

namespace App\Http\Controllers;

use App\Models\InventoryOrder;
use App\Models\InventoryOrderDetail;
use App\Models\Depot;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryOrderController extends Controller
{
    /**
     * Affiche la liste des ordres d'inventaire
     */
    public function index(Request $request)
    {
        $inventoryOrders = InventoryOrder::query()
            ->with(['depot', 'creator', 'assignedEmployee'])
            ->when($request->status, function($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->depot_id, function($query, $depotId) {
                $query->where('id_depot', $depotId);
            })
            ->when($request->employee_id, function($query, $employeeId) {
                $query->where('id_employee_assigned', $employeeId);
            })
            ->orderBy('order_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Inventory/Orders/Index', [
            'inventoryOrders' => $inventoryOrders,
            'depots' => Depot::all(),
            'employees' => Employee::all(),
            'filters' => [
                'status' => $request->status,
                'depot_id' => $request->depot_id,
                'employee_id' => $request->employee_id,
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
     * Affiche le formulaire de création d'un ordre d'inventaire
     */
    public function create()
    {
        return Inertia::render('Inventory/Orders/Create', [
            'depots' => Depot::all(),
            'employees' => Employee::all(),
            'products' => Product::all()
        ]);
    }

    /**
     * Enregistre un nouvel ordre d'inventaire
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_depot' => 'required|exists:depot,id_depot',
            'id_employee_assigned' => 'required|exists:employee,id_employee',
            'comments' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.id_product' => 'required|exists:product,id_product',
            'products.*.expected_quantity' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();

        try {
            $inventoryOrder = InventoryOrder::create([
                'order_date' => Carbon::now(),
                'id_depot' => $request->id_depot,
                'id_employee_creator' => Auth::id(),
                'id_employee_assigned' => $request->id_employee_assigned,
                'status' => 'pending',
                'comments' => $request->comments
            ]);

            foreach ($request->products as $product) {
                InventoryOrderDetail::create([
                    'id_inventory_order' => $inventoryOrder->id_inventory_order,
                    'id_product' => $product['id_product'],
                    'expected_quantity' => $product['expected_quantity']
                ]);
            }

            DB::commit();

            return redirect()->route('inventory-orders.index')
                ->with('message', 'Ordre d\'inventaire créé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de la création de l\'ordre d\'inventaire.');
        }
    }

    /**
     * Affiche les détails d'un ordre d'inventaire
     */
    public function show(InventoryOrder $inventoryOrder)
    {
        $inventoryOrder->load(['depot', 'creator', 'assignedEmployee', 'details.product']);
        
        return Inertia::render('Inventory/Orders/Show', [
            'inventoryOrder' => $inventoryOrder
        ]);
    }

    /**
     * Affiche le formulaire de modification d'un ordre d'inventaire
     */
    public function edit(InventoryOrder $inventoryOrder)
    {
        if ($inventoryOrder->status !== 'pending') {
            return redirect()->route('inventory-orders.show', $inventoryOrder)
                ->with('error', 'Vous ne pouvez pas modifier un ordre d\'inventaire qui n\'est pas en attente.');
        }

        $inventoryOrder->load(['details.product']);

        return Inertia::render('Inventory/Orders/Edit', [
            'inventoryOrder' => $inventoryOrder,
            'depots' => Depot::all(),
            'employees' => Employee::all(),
            'products' => Product::all()
        ]);
    }

    /**
     * Met à jour un ordre d'inventaire
     */
    public function update(Request $request, InventoryOrder $inventoryOrder)
    {
        if ($inventoryOrder->status !== 'pending') {
            return redirect()->route('inventory-orders.show', $inventoryOrder)
                ->with('error', 'Vous ne pouvez pas modifier un ordre d\'inventaire qui n\'est pas en attente.');
        }

        $request->validate([
            'id_depot' => 'required|exists:depot,id_depot',
            'id_employee_assigned' => 'required|exists:employee,id_employee',
            'comments' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.id_product' => 'required|exists:product,id_product',
            'products.*.expected_quantity' => 'required|integer|min:0',
            'products.*.id_inventory_order_detail' => 'nullable|exists:inventory_order_detail,id_inventory_order_detail'
        ]);

        DB::beginTransaction();

        try {
            $inventoryOrder->update([
                'id_depot' => $request->id_depot,
                'id_employee_assigned' => $request->id_employee_assigned,
                'comments' => $request->comments
            ]);

            // Supprimer les détails qui ne sont plus présents
            $detailIds = collect($request->products)
                ->pluck('id_inventory_order_detail')
                ->filter()
                ->toArray();

            InventoryOrderDetail::where('id_inventory_order', $inventoryOrder->id_inventory_order)
                ->whereNotIn('id_inventory_order_detail', $detailIds)
                ->delete();

            // Mettre à jour ou créer les détails
            foreach ($request->products as $product) {
                if (isset($product['id_inventory_order_detail'])) {
                    InventoryOrderDetail::where('id_inventory_order_detail', $product['id_inventory_order_detail'])
                        ->update([
                            'id_product' => $product['id_product'],
                            'expected_quantity' => $product['expected_quantity']
                        ]);
                } else {
                    InventoryOrderDetail::create([
                        'id_inventory_order' => $inventoryOrder->id_inventory_order,
                        'id_product' => $product['id_product'],
                        'expected_quantity' => $product['expected_quantity']
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('inventory-orders.show', $inventoryOrder)
                ->with('message', 'Ordre d\'inventaire mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour de l\'ordre d\'inventaire.');
        }
    }

    /**
     * Démarre un ordre d'inventaire
     */
    public function start(InventoryOrder $inventoryOrder)
    {
        if ($inventoryOrder->status !== 'pending') {
            return back()->with('error', 'Vous ne pouvez pas démarrer un ordre d\'inventaire qui n\'est pas en attente.');
        }

        $inventoryOrder->update([
            'status' => 'in_progress',
            'start_date' => Carbon::now()
        ]);

        return back()->with('message', 'Ordre d\'inventaire démarré avec succès.');
    }

    /**
     * Complète un ordre d'inventaire
     */
    public function complete(Request $request, InventoryOrder $inventoryOrder)
    {
        if ($inventoryOrder->status !== 'in_progress') {
            return back()->with('error', 'Vous ne pouvez pas terminer un ordre d\'inventaire qui n\'est pas en cours.');
        }

        $request->validate([
            'details' => 'required|array',
            'details.*.id_inventory_order_detail' => 'required|exists:inventory_order_detail,id_inventory_order_detail',
            'details.*.counted_quantity' => 'required|integer|min:0',
            'details.*.comments' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $inventoryOrder->update([
                'status' => 'completed',
                'completion_date' => Carbon::now()
            ]);

            foreach ($request->details as $detail) {
                $orderDetail = InventoryOrderDetail::findOrFail($detail['id_inventory_order_detail']);
                $orderDetail->update([
                    'counted_quantity' => $detail['counted_quantity'],
                    'difference' => $detail['counted_quantity'] - $orderDetail->expected_quantity,
                    'comments' => $detail['comments'] ?? null
                ]);

                // Mettre à jour l'inventaire
                $inventory = Inventory::firstOrNew([
                    'id_depot' => $inventoryOrder->id_depot,
                    'id_product' => $orderDetail->id_product
                ]);

                $inventory->quantity = $detail['counted_quantity'];
                $inventory->last_updated = Carbon::now();
                $inventory->last_updated_by = Auth::id();
                $inventory->save();
            }

            DB::commit();

            return redirect()->route('inventory-orders.show', $inventoryOrder)
                ->with('message', 'Ordre d\'inventaire complété avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Une erreur est survenue lors de la complétion de l\'ordre d\'inventaire.');
        }
    }

    /**
     * Annule un ordre d'inventaire
     */
    public function cancel(InventoryOrder $inventoryOrder)
    {
        if (!in_array($inventoryOrder->status, ['pending', 'in_progress'])) {
            return back()->with('error', 'Vous ne pouvez pas annuler un ordre d\'inventaire qui est déjà terminé ou annulé.');
        }

        $inventoryOrder->update([
            'status' => 'cancelled'
        ]);

        return back()->with('message', 'Ordre d\'inventaire annulé avec succès.');
    }

    /**
     * Supprime un ordre d'inventaire
     */
    public function destroy(InventoryOrder $inventoryOrder)
    {
        if ($inventoryOrder->status !== 'pending') {
            return back()->with('error', 'Vous ne pouvez supprimer que les ordres d\'inventaire en attente.');
        }

        $inventoryOrder->details()->delete();
        $inventoryOrder->delete();

        return redirect()->route('inventory-orders.index')
            ->with('message', 'Ordre d\'inventaire supprimé avec succès.');
    }
}