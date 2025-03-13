<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Mail\DocumentMail;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = Document::query()
            ->with(['customer'])
            ->when($request->customer_id, function($query, $customerId) {
                $query->where('id_customer', $customerId);
            })
            ->when($request->document_type, function($query, $type) {
                $query->where('document_type', $type);
            })
            ->when($request->payment_status, function($query, $status) {
                $query->where('payment_status', $status);
            })
            ->when($request->search, function($query, $search) {
                $query->where(function($query) use ($search) {
                    $query->where('document_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function($query) use ($search) {
                            $query->where('customer_name', 'like', "%{$search}%")
                                ->orWhere('customer_firstname', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->start_date && $request->end_date, function($query) use ($request) {
                $query->whereBetween('document_date', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            })
            ->orderBy('document_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'customers' => Customer::select('id_customer', 'customer_name', 'customer_firstname')->get(),
            'documentTypes' => $this->getDocumentTypes(),
            'paymentStatuses' => $this->getPaymentStatuses(),
            'paymentMethods' => $this->getPaymentMethods(),
            'filters' => [
                'customer_id' => $request->customer_id,
                'document_type' => $request->document_type,
                'payment_status' => $request->payment_status,
                'search' => $request->search,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]
        ]);
    }

    public function show(Document $document)
    {
        $document->load(['customer', 'order.orderDetails.product']);
        
        return Inertia::render('Documents/Show', [
            'document' => $document
        ]);
    }

    public function printDocument(Document $document)
    {
        $document->load(['customer', 'order.orderDetails.product']);
        
        $view = 'documents.' . $document->document_type;
        
        $pdf = Pdf::loadView($view, [
            'document' => $document
        ]);
        
        return $pdf->stream($document->document_type . '-' . $document->document_number . '.pdf');
    }

    public function emailDocument(Document $document)
    {
        $document->load(['customer', 'order.orderDetails.product']);
        
        if (!$document->customer->customer_email) {
            return back()->with('error', 'Ce client n\'a pas d\'adresse email.');
        }
        
        Mail::to($document->customer->customer_email)
            ->send(new DocumentMail($document));
        
        return back()->with('message', 'Le document a été envoyé par email.');
    }

    public function markAsPaid(Document $document, Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|max:20',
            'payment_date' => 'required|date'
        ]);
        
        $document->update([
            'payment_status' => 'paid',
            'payment_method' => $validated['payment_method'],
            'payment_date' => $validated['payment_date']
        ]);
        
        return back()->with('message', 'Document marqué comme payé.');
    }

    public function report(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfDay();

        $documents = Document::with('customer')
            ->whereBetween('document_date', [$startDate, $endDate])
            ->when($request->document_type, function($query, $type) {
                $query->where('document_type', $type);
            })
            ->when($request->payment_status, function($query, $status) {
                $query->where('payment_status', $status);
            })
            ->when($request->customer_id, function($query, $customerId) {
                $query->where('id_customer', $customerId);
            })
            ->orderBy('document_date', 'desc')
            ->get();

        // Calcul des statistiques
        $stats = [
            'total_documents' => $documents->count(),
            'total_amount' => $documents->sum('total_amount'),
            'paid_amount' => $documents->where('payment_status', 'paid')->sum('total_amount'),
            'pending_amount' => $documents->where('payment_status', 'pending')->sum('total_amount'),
            'by_type' => $documents->groupBy('document_type')->map->count(),
            'by_status' => $documents->groupBy('payment_status')->map->count(),
            'by_payment_method' => $documents->where('payment_status', 'paid')->groupBy('payment_method')->map->count(),
        ];

        return Inertia::render('Documents/Report', [
            'documents' => $documents,
            'stats' => $stats,
            'customers' => Customer::select('id_customer', 'customer_name', 'customer_firstname')->get(),
            'documentTypes' => $this->getDocumentTypes(),
            'paymentStatuses' => $this->getPaymentStatuses(),
            'paymentMethods' => $this->getPaymentMethods(),
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'document_type' => $request->document_type,
                'payment_status' => $request->payment_status,
                'customer_id' => $request->customer_id
            ]
        ]);
    }

    private function getDocumentTypes()
    {
        return [
            'invoice' => 'Facture',
            'delivery_note' => 'Bon de livraison',
            'return_note' => 'Note de retour'
        ];
    }

    private function getPaymentStatuses()
    {
        return [
            'pending' => 'En attente',
            'paid' => 'Payé',
            'cancelled' => 'Annulé'
        ];
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
}