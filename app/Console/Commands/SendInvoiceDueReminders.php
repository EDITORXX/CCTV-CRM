<?php

namespace App\Console\Commands;

use App\Mail\InvoiceDueReminderMail;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendInvoiceDueReminders extends Command
{
    protected $signature = 'invoices:send-due-reminders';

    protected $description = 'Send due-date reminders for invoices with pending remaining amount.';

    public function handle(): int
    {
        $today = now()->toDateString();

        $invoices = Invoice::query()
            ->with(['company.users', 'customer'])
            ->withSum('payments', 'amount')
            ->whereDate('remaining_due_date', $today)
            ->whereNull('due_reminder_sent_at')
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->get();

        $sentCount = 0;

        foreach ($invoices as $invoiceRecord) {
            /** @var Invoice $invoice */
            $invoice = $invoiceRecord;
            $paidAmount = (float) ($invoice->payments_sum_amount ?? 0);
            $outstandingAmount = max(0, (float) $invoice->total - $paidAmount);

            if ($outstandingAmount <= 0) {
                continue;
            }

            $company = $invoice->company;
            if (!$company) {
                continue;
            }

            $recipientEmails = $company->users()
                ->wherePivot('role', 'company_admin')
                ->whereNotNull('email')
                ->pluck('email')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($recipientEmails) && !empty($company->email)) {
                $recipientEmails = [$company->email];
            }

            if (empty($recipientEmails)) {
                $this->warn("No admin email found for invoice {$invoice->invoice_number}");
                continue;
            }

            Mail::to($recipientEmails)->send(new InvoiceDueReminderMail($invoice, $outstandingAmount));

            $invoice->update([
                'due_reminder_sent_at' => now(),
            ]);

            $sentCount++;
        }

        $this->info("Due reminders sent: {$sentCount}");

        return self::SUCCESS;
    }
}
