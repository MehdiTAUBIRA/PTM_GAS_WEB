<?php

namespace App\Http\Controllers;

use App\Mail\DepositReceiptMail;
use App\Mail\DepositReturnReceiptMail;
use App\Models\CustomerDeposit;
use App\Models\DepositDocument;
use App\Models\DepositRate;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepositController extends Controller
{
    public function index(Request $request)
    {
        $deposits = CustomerDeposit::query()
            ->with(['customer', 'product', 'employee'])
            ->when($request->search, function($query, $search) {
                $query->whereHas('customer', function($query) use ($search) {
                    $query->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_firstname', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function($query, $status) {
                if ($status === 'paid') {
                    $query->where('deposit_paid', true);
                } elseif ($status === 'unpaid') {
                    $query->where('deposit_paid', false);
                }
            })
            ->orderBy('deposit_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Deposits/Index', [
            'deposits' => $deposits,
            'paymentMethods' => $this->getPaymentMethods()
        ]);
    }

    public function create()
    {
        return Inertia::render('Deposits/Create', [
            'customers' => Customer::all(),
            'products' => Product::all(),
            'employees' => Employee::all(),
            'paymentMethods' => $this->getPaymentMethods(),
            'depositRates' => DepositRate::where('is_active', true)->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_customer' => 'required|exists:customer,id_customer',
            'id_product' => 'required|exists:product,id_product',
            'deposit_paid' => 'required|boolean',
            'deposit_amount' => 'required|numeric|min:0',
            'deposit_date' => 'required|date',
            'deposit_payment_method' => 'required_if:deposit_paid,true',
            'id_order' => 'nullable|exists:orders,id_order',
            'id_employee' => 'required|exists:employee,id_employee',
            'comments' => 'nullable|string',
            'generate_receipt' => 'boolean'
        ]);

        $deposit = CustomerDeposit::create($validated);

        // Generate receipt if requested
        if ($request->generate_receipt && $validated['deposit_paid']) {
            $documentNumber = 'DEP-' . date('Ymd') . '-' . uniqid();
            
            DepositDocument::create([
                'document_number' => $documentNumber,
                'document_type' => 'deposit_receipt',
                'id_customer' => $validated['id_customer'],
                'id_customer_deposit' => $deposit->id_customer_deposit,
                'document_date' => Carbon::now(),
                'amount' => $validated['deposit_amount'],
                'created_by' => $validated['id_employee']
            ]);
        }

        return redirect()->route('deposits.index')
            ->with('message', 'Dépôt créé avec succès');
    }

    public function report()
    {
        $stats = [
            'total_deposits' => CustomerDeposit::count(),
            'paid_deposits' => CustomerDeposit::where('deposit_paid', true)->count(),
            'pending_deposits' => CustomerDeposit::where('deposit_paid', false)->count(),
            'total_amount' => CustomerDeposit::sum('deposit_amount'),
            'paid_amount' => CustomerDeposit::where('deposit_paid', true)->sum('deposit_amount'),
            'pending_amount' => CustomerDeposit::where('deposit_paid', false)->sum('deposit_amount'),
            'payment_methods' => DB::table('customer_deposits')
                ->select('deposit_payment_method', DB::raw('count(*) as count'))
                ->where('deposit_paid', true)
                ->groupBy('deposit_payment_method')
                ->get(),
            'deposits_by_product' => DB::table('customer_deposits')
                ->select('product.prolibcommercial', DB::raw('count(*) as count'))
                ->join('product', 'product.id_product', '=', 'customer_deposits.id_product')
                ->groupBy('product.prolibcommercial')
                ->get(),
        ];

        $depositDocuments = DepositDocument::with(['customer', 'customerDeposit', 'employee'])
            ->orderBy('document_date', 'desc')
            ->get()
            ->groupBy('document_type');

        return Inertia::render('Deposits/Report', [
            'stats' => $stats,
            'depositDocuments' => $depositDocuments,
            'paymentMethods' => $this->getPaymentMethods()
        ]);
    }

    public function returnDeposit(Request $request)
    {
        $validated = $request->validate([
            'id_customer_deposit' => 'required|exists:customer_deposits,id_customer_deposit',
            'id_employee' => 'required|exists:employee,id_employee',
            'comments' => 'nullable|string'
        ]);

        $deposit = CustomerDeposit::findOrFail($validated['id_customer_deposit']);
        
        if (!$deposit->deposit_paid) {
            return back()->with('error', 'Ce dépôt n\'a pas encore été payé.');
        }

        // Create return receipt
        $documentNumber = 'RET-' . date('Ymd') . '-' . uniqid();
        
        DepositDocument::create([
            'document_number' => $documentNumber,
            'document_type' => 'return_receipt',
            'id_customer' => $deposit->id_customer,
            'id_customer_deposit' => $deposit->id_customer_deposit,
            'document_date' => Carbon::now(),
            'amount' => -$deposit->deposit_amount, // Negative amount for return
            'created_by' => $validated['id_employee']
        ]);

        $deposit->update([
            'status' => 'returned',
            'comments' => $deposit->comments . "\n[" . Carbon::now() . "] RETURNED: " . ($validated['comments'] ?? '')
        ]);

        return redirect()->route('deposits.index')
            ->with('message', 'Dépôt remboursé avec succès');
    }

    private function getPaymentMethods()
    {
        return [
            'cash' => 'Espèces',
            'card' => 'Carte bancaire',
            'transfer' => 'Virement',
            'check' => 'Chèque'
        ];
    }

    public function emailReceipt(CustomerDeposit $deposit)
    {
        $document = DepositDocument::where('id_customer_deposit', $deposit->id_customer_deposit)
            ->where('document_type', 'deposit_receipt')
            ->first();
        
        if (!$document) {
            return back()->with('error', 'Aucun reçu trouvé pour ce dépôt.');
        }
        
        // Envoi de l'email avec le reçu en pièce jointe
        Mail::to($deposit->customer->customer_email)
            ->send(new DepositReceiptMail($deposit, $document));
        
        return back()->with('message', 'Le reçu a été envoyé par email.');
    }

    public function printReceipt(CustomerDeposit $deposit)
    {
        $document = DepositDocument::where('id_customer_deposit', $deposit->id_customer_deposit)
            ->where('document_type', 'deposit_receipt')
            ->first();
        
        if (!$document) {
            return back()->with('error', 'Aucun reçu trouvé pour ce dépôt.');
        }
        
        $pdf = Pdf::loadView('deposits.receipt', [
            'deposit' => $deposit,
            'document' => $document
        ]);
        
        return $pdf->stream('recu-depot-' . $document->document_number . '.pdf');
    }

    public function emailReturnReceipt(CustomerDeposit $deposit)
    {
        $document = DepositDocument::where('id_customer_deposit', $deposit->id_customer_deposit)
            ->where('document_type', 'return_receipt')
            ->first();
        
        if (!$document) {
            return back()->with('error', 'Aucun reçu de remboursement trouvé pour ce dépôt.');
        }
        
        // Envoi de l'email avec le reçu en pièce jointe
        Mail::to($deposit->customer->customer_email)
            ->send(new DepositReturnReceiptMail($deposit, $document));
        
        return back()->with('message', 'Le reçu de remboursement a été envoyé par email.');
    }
}