<?php

namespace App\Mail;

use App\Models\CustomerDeposit;
use App\Models\DepositDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class DepositReturnReceiptMail extends Mailable
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
        $pdf = Pdf::loadView('deposits.return_receipt', [
            'deposit' => $this->deposit,
            'document' => $this->document
        ]);

        $filename = 'recu-remboursement-' . $this->document->document_number . '.pdf';

        return $this->subject('Reçu de remboursement de dépôt #' . $this->document->document_number)
            ->view('emails.deposit_return_receipt', [
                'deposit' => $this->deposit,
                'document' => $this->document,
                'customerName' => $this->deposit->customer->customer_firstname . ' ' . $this->deposit->customer->customer_name
            ])
            ->attachData($pdf->output(), $filename, [
                'mime' => 'application/pdf',
            ]);
    }
}