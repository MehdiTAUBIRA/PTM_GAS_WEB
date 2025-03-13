<?php

namespace App\Mail;

use App\Models\CustomerDeposit;
use App\Models\DepositDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class DepositReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $deposit;
    protected $document;

    public function __construct(CustomerDeposit $deposit, DepositDocument $document)
    {
        $this->deposit = $deposit;
        $this->document = $document;
    }

    public function build()
    {
        $pdf = Pdf::loadView('deposits.receipt', [
            'deposit' => $this->deposit,
            'document' => $this->document
        ]);

        $filename = 'recu-depot-' . $this->document->document_number . '.pdf';

        return $this->subject('Reçu de dépôt de garantie #' . $this->document->document_number)
            ->view('emails.deposit_receipt', [
                'deposit' => $this->deposit,
                'document' => $this->document,
                'customerName' => $this->deposit->customer->customer_firstname . ' ' . $this->deposit->customer->customer_name
            ])
            ->attachData($pdf->output(), $filename, [
                'mime' => 'application/pdf',
            ]);
    }
}