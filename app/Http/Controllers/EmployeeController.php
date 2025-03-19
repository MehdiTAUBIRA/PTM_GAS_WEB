<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Depot;
use Illuminate\Http\Request;
use Inertia\Inertia;
use libphonenumber\PhoneNumberUtil;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::query()
            ->with(['depot'])  // Correction ici : with prend un array
            ->when($request->search, function($query, $search) {
                $query->where(function($query) use ($search) {
                    $query->where('employee_firstname', 'like', "%{$search}%")
                        ->orWhere('employee_lastname', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('employee_email', 'like', "%{$search}%");
                });
            })
            ->orderBy('employee_lastname')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Employees/Index', [
            'employees' => $employees
        ]);
    }

    public function create()
    {
        return Inertia::render('Employees/Create', [
            'depots' => Depot::all(),
            'roles' => [
                'admin' => 'Administrateur',
                'manager' => 'Manager',
                'inventory_clerk' => 'Gestionnaire de stock',
                'warehouse_worker' => 'Employé d\'entrepôt'
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_code' => 'required|Regex:/^[\D]+$/i|max:10|unique:employee,employee_code',
            'employee_firstname' => 'required |Regex:/^[\D]+$/i|max:50',
            'employee_lastname' => 'required|Regex:/^[\D]+$/i||max:50',
            'employee_email' => 'required|email:rfc|max:100|unique:employee,employee_email',
            'employee_phone' => 'nullable|phone:FR,US,NATIONAL|unique:employee,employee_phone',
            'employee_role' => 'required|in:admin,manager,inventory_clerk,warehouse_worker',
            'id_depot' => 'required|exists:depot,id_depot',
            'hire_date' => 'required|date',
            'username' => 'required|max:50|unique:employee,username',
            'password' => 'required|min:8|max:100'
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['status'] = true;

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('message', 'Employé créé avec succès');
    }

    public function destroy(Employee $employee)
    {
        $employee->status = false;
        $employee->save();
        
        return redirect()->back()
            ->with('message', 'Employé désactivé avec succès');
    }
}