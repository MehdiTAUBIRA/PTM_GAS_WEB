<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductAttribut;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['attributes'])
            ->when($request->search, function($query, $search) {
                $query->where(function($query) use ($search) {
                    $query->where('procode', 'like', "%{$search}%")
                        ->orWhere('prolibcommercial', 'like', "%{$search}%");
                });
            })
            ->orderBy('procode')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'productTypes' => $this->getProductTypes(),
            'unitTypes' => $this->getUnitTypes()
        ]);
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'productTypes' => $this->getProductTypes(),
            'unitTypes' => $this->getUnitTypes(),
            'valveTypes' => $this->getValveTypes()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'procode' => 'required|max:5|unique:product,procode',
            'protype' => 'required|integer',
            'unitcode' => 'required|integer',
            'proprice' => 'required|numeric',
            'prolibcommercial' => 'required|max:50',
            'pro_real_capacity' => 'required|numeric',
            'attributes' => 'array',
            'attributes.*.barcode' => 'required|max:25',
            'attributes.*.serial_number' => 'required|max:20',
            'attributes.*.ownership' => 'nullable|max:20',
            'attributes.*.manufacture_date' => 'required|date',
            'attributes.*.expiration_date' => 'nullable|date',
            'attributes.*.valve_type' => 'required|max:3',
            'attributes.*.manufacturer' => 'nullable|max:50',
            'attributes.*.last_test_date' => 'nullable|date',
            'attributes.*.state' => 'required|max:1'
        ]);

        $product = Product::create([
            'procode' => $validated['procode'],
            'protype' => $validated['protype'],
            'unitcode' => $validated['unitcode'],
            'proprice' => $validated['proprice'],
            'prolibcommercial' => $validated['prolibcommercial'],
            'pro_real_capacity' => $validated['pro_real_capacity']
        ]);

        if (isset($validated['attributes'])) {
            foreach ($validated['attributes'] as $attribute) {
                $product->attributes()->create($attribute);
            }
        }

        return redirect()->route('products.index')
            ->with('message', 'Produit créé avec succès');
    }

    private function getProductTypes()
    {
        return [
            1 => 'Type 1',
            2 => 'Type 2',
            // Ajoutez vos types de produits ici
        ];
    }

    private function getUnitTypes()
    {
        return [
            1 => 'Unité 1',
            2 => 'Unité 2',
            // Ajoutez vos types d'unités ici
        ];
    }

    private function getValveTypes()
    {
        return [
            'A' => 'Type A',
            'B' => 'Type B',
            'C' => 'Type C',
            // Ajoutez vos types de valves ici
        ];
    }
}