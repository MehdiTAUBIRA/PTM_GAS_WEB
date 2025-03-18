<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepotController extends Controller
{
    public function index(Request $request)
{
    $depots = Depot::query()
        ->when($request->search, function($query, $search) {
            $query->where(function($query) use ($search) {
                $query->where('depotlabel', 'like', "%{$search}%")
                    ->orWhere('depotcode', 'like', "%{$search}%")
                    ->orWhere('depotcity', 'like', "%{$search}%");
            });
        })
        ->orderBy('depotlabel')
        ->paginate(10)
        ->withQueryString();

    return Inertia::render('Depots/Index', [
        'depots' => $depots
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'depotpostalcode' => 'required|max:10',
            'depotcode' => 'required|max:4|unique:depot',
            'depotlabel' => 'required|max:30',
            'depotaddress' => 'required|max:50',
            'depotcity' => 'required|max:30',
            'depotphone' => 'required|max:20',
            'depotgpsx' => 'nullable|numeric',
            'depotgpsy' => 'nullable|numeric',
        ]);

        Depot::create($validated);

        return redirect()->back()
            ->with('message', 'Dépôt créé avec succès');
    }

    public function create()
    {
        return Inertia::render('Depots/Create');
    }

    public function destroy(Depot $depot)
    {
        $depot->delete();
        
        return redirect()->back()->with('message', 'Dépôt supprimé avec succès');
    }
}
