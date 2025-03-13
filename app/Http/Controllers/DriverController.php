<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $drivers = Driver::query()
            ->when($request->search, function($query, $search) {
                $query->where(function($query) use ($search) {
                    $query->where('drivername', 'like', "%{$search}%")
                        ->orWhere('driversurname', 'like', "%{$search}%")
                        ->orWhere('drivercode', 'like', "%{$search}%")
                        ->orWhere('driveremail', 'like', "%{$search}%");
                });
            })
            ->orderBy('drivername')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Drivers/Index', [
            'drivers' => $drivers
        ]);
    }

    public function create()
    {
        return Inertia::render('Drivers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'drivercode' => 'required|max:5|unique:driver,drivercode',
            'drivername' => 'required|max:50',
            'driversurname' => 'required|max:50',
            'driverlastmedicalvisit' => 'required|date',
            'driverlastlicensecontrol' => 'required|date',
            'driveraccreditation' => 'required|max:20',
            'driveraddress' => 'required|max:50',
            'driveraddressnext' => 'required|max:50',
            'driverphone' => 'required|max:20',
            'drivercity' => 'required|max:80',
            'driverpostalcode' => 'required|max:10',
            'drivercountry' => 'nullable|max:3',
            'drivercomments' => 'required|max:30',
            'driveremail' => 'required|email|max:100|unique:driver,driveremail',
            'drivermobile' => 'required|max:20',
            'driverattestationdate' => 'required|date',
            'drivernextmedicalvisit' => 'required|date',
            'drivernextlicensecontrol' => 'required|date',
            'username' => 'nullable|max:50|unique:driver,username',
            'driverpassword' => 'nullable|max:15',
        ]);

        $validated['driver_status'] = true;

        Driver::create($validated);

        return redirect()->route('drivers.index')
            ->with('message', 'Chauffeur créé avec succès');
    }

    public function destroy(Driver $driver)
    {
        $driver->driver_status = false;
        $driver->save();
        
        return redirect()->back()
            ->with('message', 'Chauffeur désactivé avec succès');
    }
}