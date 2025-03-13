<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CylinderController;
use App\Http\Controllers\CylinderMaintenanceController;
use App\Http\Controllers\CylinderMovementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryRouteController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\DepotController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryOrderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReportController;
use App\Http\Controllers\ProductTrackingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    /*return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);*/
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/about', fn () => Inertia::render('About'))->name('about');

    Route::get('users', [UserController::class, 'index'])->name('users.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/depots', [DepotController::class, 'index'])->name('depots.index');
    Route::get('/depots/create', [DepotController::class, 'create'])->name('depots.create');
    Route::post('/depots', [DepotController::class, 'store'])->name('depots.store');
    Route::get('/depots/{depot}/edit', [DepotController::class, 'edit'])->name('depots.edit');
    Route::put('/depots/{depot}', [DepotController::class, 'update'])->name('depots.update');
    Route::delete('/depots/{depot}', [DepotController::class, 'destroy'])->name('depots.destroy');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
    Route::get('/drivers/create', [DriverController::class, 'create'])->name('drivers.create');
    Route::post('/drivers', [DriverController::class, 'store'])->name('drivers.store');
    Route::delete('/drivers/{driver}', [DriverController::class, 'destroy'])->name('drivers.destroy');

    Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/orders/report', [OrderController::class, 'report'])->name('orders.report');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
    Route::get('/deposits/create', [DepositController::class, 'create'])->name('deposits.create');
    Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
    Route::get('/deposits/report', [DepositController::class, 'report'])->name('deposits.report');
    Route::post('/deposits/return', [DepositController::class, 'returnDeposit'])->name('deposits.return');
    Route::get('/deposits/{deposit}/receipt/print', [DepositController::class, 'printReceipt'])->name('deposits.receipt.print');
    Route::post('/deposits/{deposit}/receipt/email', [DepositController::class, 'emailReceipt'])->name('deposits.receipt.email');
    Route::get('/deposits/{deposit}/return-receipt/print', [DepositController::class, 'printReturnReceipt'])->name('deposits.return.receipt.print');
    Route::post('/deposits/{deposit}/return-receipt/email', [DepositController::class, 'emailReturnReceipt'])->name('deposits.return.receipt.email');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/report', [DocumentController::class, 'report'])->name('documents.report');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/documents/{document}/print', [DocumentController::class, 'printDocument'])->name('documents.print');
    Route::post('/documents/{document}/email', [DocumentController::class, 'emailDocument'])->name('documents.email');
    Route::patch('/documents/{document}/mark-as-paid', [DocumentController::class, 'markAsPaid'])->name('documents.mark-as-paid');

    Route::get('/inventory-orders', [InventoryOrderController::class, 'index'])->name('inventory-orders.index');
    Route::get('/inventory-orders/create', [InventoryOrderController::class, 'create'])->name('inventory-orders.create');
    Route::post('/inventory-orders', [InventoryOrderController::class, 'store'])->name('inventory-orders.store');
    Route::get('/inventory-orders/{inventoryOrder}', [InventoryOrderController::class, 'show'])->name('inventory-orders.show');
    Route::get('/inventory-orders/{inventoryOrder}/edit', [InventoryOrderController::class, 'edit'])->name('inventory-orders.edit');
    Route::put('/inventory-orders/{inventoryOrder}', [InventoryOrderController::class, 'update'])->name('inventory-orders.update');
    Route::delete('/inventory-orders/{inventoryOrder}', [InventoryOrderController::class, 'destroy'])->name('inventory-orders.destroy');
    Route::post('/inventory-orders/{inventoryOrder}/start', [InventoryOrderController::class, 'start'])->name('inventory-orders.start');
    Route::post('/inventory-orders/{inventoryOrder}/complete', [InventoryOrderController::class, 'complete'])->name('inventory-orders.complete');
    Route::post('/inventory-orders/{inventoryOrder}/cancel', [InventoryOrderController::class, 'cancel'])->name('inventory-orders.cancel');
    
    // Routes pour l'état de l'inventaire
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/report', [InventoryController::class, 'report'])->name('inventory.report');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

    
    // Maintenance des bouteilles
    Route::get('/cylinder-maintenance', [CylinderMaintenanceController::class, 'index'])->name('cylinder-maintenance.index');
    Route::get('/cylinder-maintenance/create', [CylinderMaintenanceController::class, 'create'])->name('cylinder-maintenance.create');
    Route::post('/cylinder-maintenance', [CylinderMaintenanceController::class, 'store'])->name('cylinder-maintenance.store');
    Route::get('/cylinder-maintenance/{maintenance}', [CylinderMaintenanceController::class, 'show'])->name('cylinder-maintenance.show');
    Route::get('/cylinder-maintenance/{maintenance}/edit', [CylinderMaintenanceController::class, 'edit'])->name('cylinder-maintenance.edit');
    Route::put('/cylinder-maintenance/{maintenance}', [CylinderMaintenanceController::class, 'update'])->name('cylinder-maintenance.update');
    Route::delete('/cylinder-maintenance/{maintenance}', [CylinderMaintenanceController::class, 'destroy'])->name('cylinder-maintenance.destroy');
    Route::post('/cylinder-maintenance/complete/{maintenance}', [CylinderMaintenanceController::class, 'complete'])->name('cylinder-maintenance.complete');
    Route::get('/cylinder-maintenance/schedule', [CylinderMaintenanceController::class, 'scheduleMaintenance'])->name('cylinder-maintenance.schedule');

    Route::get('/product-tracking', [ProductTrackingController::class, 'index'])->name('product-tracking.index');
    Route::get('/product-tracking/{id}', [ProductTrackingController::class, 'productHistory'])->name('product-tracking.history');
    Route::get('/products/report', [ProductReportController::class, 'index'])->name('products.report');
    Route::get('/products/report/{id}', [ProductReportController::class, 'productDetails'])->name('products.report.details');

    Route::get('/stocks', [StockController::class, 'overview'])->name('stocks.overview');
    Route::get('/stocks/by-depot', [StockController::class, 'byDepot'])->name('stocks.by-depot');
    Route::get('/stocks/alerts', [StockController::class, 'alerts'])->name('stocks.alerts');
    Route::post('/stocks/alerts', [StockController::class, 'storeAlert'])->name('stocks.alerts.store');
    Route::put('/stocks/alerts/{id}', [StockController::class, 'updateAlert'])->name('stocks.alerts.update');
    Route::delete('/stocks/alerts/{id}', [StockController::class, 'deleteAlert'])->name('stocks.alerts.delete');
    Route::get('/stocks/damaged', [StockController::class, 'damaged'])->name('stocks.damaged');
    Route::get('/stocks/forecast', [StockController::class, 'forecast'])->name('stocks.forecast');

    Route::get('/delivery-routes', [DeliveryRouteController::class, 'index'])->name('delivery-routes.index');
    Route::get('/delivery-routes/create', [DeliveryRouteController::class, 'create'])->name('delivery-routes.create');
    Route::post('/delivery-routes', [DeliveryRouteController::class, 'store'])->name('delivery-routes.store');
    Route::get('/delivery-routes/{id}', [DeliveryRouteController::class, 'show'])->name('delivery-routes.show');
    Route::get('/delivery-routes/{id}/edit', [DeliveryRouteController::class, 'edit'])->name('delivery-routes.edit');
    Route::put('/delivery-routes/{id}', [DeliveryRouteController::class, 'update'])->name('delivery-routes.update');
    Route::delete('/delivery-routes/{id}', [DeliveryRouteController::class, 'destroy'])->name('delivery-routes.destroy');
    Route::put('/delivery-routes/{id}/stops/{stopId}', [DeliveryRouteController::class, 'updateStopStatus'])->name('delivery-routes.update-stop');
    
    // Vue spéciale pour les chauffeurs
    Route::get('/driver-routes/{driverId}', [DeliveryRouteController::class, 'driverRoutes'])->name('driver-routes');

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');

});

require __DIR__.'/auth.php';
