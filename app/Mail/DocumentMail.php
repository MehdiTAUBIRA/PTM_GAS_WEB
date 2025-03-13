<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function build()
    {
        $view = 'documents.' . $this->document->document_type;
        
        $pdf = Pdf::loadView($view, [
            'document' => $this->document
        ]);

        $filename = $this->document->document_type . '-' . $this->document->document_number . '.pdf';
        
        $documentTypes = [
            'invoice' => 'Facture',
            'delivery_note' => 'Bon de livraison',
            'return_note' => 'Note de retour'
        ];

        $documentName = $documentTypes[$this->document->document_type] ?? 'Document';

        return $this->subject($documentName . ' #' . $this->document->document_number)
            ->view('emails.document', [
                'document' => $this->document,
                'documentName' => $documentName,
                'customerName' => $this->document->customer->customer_firstname . ' ' . $this->document->customer->customer_name
            ])
            ->attachData($pdf->output(), $filename, [
                'mime' => 'application/pdf',
            ]);
    }
}