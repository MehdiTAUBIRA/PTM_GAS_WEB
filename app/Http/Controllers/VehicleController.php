<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Depot;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::query()
            ->with(['driver', 'depot'])
            ->when($request->search, function($query, $search) {
                $query->where(function($query) use ($search) {
                    $query->where('vehiclename', 'like', "%{$search}%")
                        ->orWhere('vehicleimmat', 'like', "%{$search}%")
                        ->orWhere('vehiclecode', 'like', "%{$search}%");
                });
            })
            ->orderBy('vehiclename')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Vehicles/Index', [
            'vehicles' => $vehicles
        ]);
    }

    public function create()
    {
        return Inertia::render('Vehicles/Create', [
            'drivers' => Driver::where('driver_status', true)->get(),
            'depots' => Depot::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehiclecode' => 'required|max:20',
            'vehiclename' => 'required|max:50',
            'vehicleimmat' => 'required|max:20',
            'vehiclelastcontroldate' => 'nullable|date',
            'vehiclenextcontroldate' => 'nullable|date',
            'vehicletotalcapacity' => 'required|numeric',
            'vehiclecontractnumber' => 'nullable|max:20',
            'vehiclecontractlabel' => 'nullable|max:30',
            'vehiclecontractdatestart' => 'nullable|date',
            'vehiclecontractdateend' => 'nullable|date',
            'vehicleweigth' => 'required|numeric',
            'vehiclemaxweigth' => 'required|numeric',
            'id_driver' => 'nullable|exists:driver,id_driver',
            'id_depot' => 'required|exists:depot,id_depot'
        ]);

        Vehicle::create($validated);

        return redirect()->route('vehicles.index')
            ->with('message', 'Véhicule créé avec succès');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->back()
            ->with('message', 'Véhicule supprimé avec succès');
    }
}