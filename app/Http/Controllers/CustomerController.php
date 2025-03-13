<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->with(['addresses'])
            ->when($request->search, function($query, $search) {
                $query->where(function($query) use ($search) {
                    $query->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_firstname', 'like', "%{$search}%")
                        ->orWhere('customer_email', 'like', "%{$search}%");
                });
            })
            ->orderBy('customer_name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Customers/Index', [
            'customers' => $customers
        ]);
    }

    public function create()
    {
        return Inertia::render('Customers/Create', [
            'addressTypes' => [
                'billing' => 'Facturation',
                'delivery' => 'Livraison',
                'other' => 'Autre'
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|max:30',
            'customer_firstname' => 'required|max:30',
            'customer_phone' => 'required|max:20',
            'customer_email' => 'required|email|max:150|unique:customer,customer_email',
            'addresses' => 'required|array|min:1',
            'addresses.*.address' => 'required|max:150',
            'addresses.*.postalcode' => 'required|max:10',
            'addresses.*.country' => 'required|max:50',
            'addresses.*.address_type' => 'required|max:50'
        ]);

        $customer = Customer::create([
            'customer_name' => $validated['customer_name'],
            'customer_firstname' => $validated['customer_firstname'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'],
        ]);

        foreach ($validated['addresses'] as $address) {
            $customer->addresses()->create($address);
        }

        return redirect()->route('customers.index')
            ->with('message', 'Client créé avec succès');
    }

    public function destroy(Customer $customer)
    {
        $customer->addresses()->delete();
        $customer->delete();
        
        return redirect()->back()
            ->with('message', 'Client supprimé avec succès');
    }
}