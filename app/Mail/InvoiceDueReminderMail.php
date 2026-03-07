<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceDueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    public float $outstandingAmount;

    public function __construct(Invoice $invoice, float $outstandingAmount)
    {
        $this->invoice = $invoice;
        $this->outstandingAmount = $outstandingAmount;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice Due Reminder: ' . $this->invoice->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-due-reminder',
        );
    }
}
