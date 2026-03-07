<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Due Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; color: #222; line-height: 1.5;">
    <h2 style="margin-bottom: 12px;">Invoice Due Reminder</h2>
    <p style="margin: 0 0 10px 0;">
        This is a reminder that an invoice has reached its remaining due date today.
    </p>

    <table cellpadding="6" cellspacing="0" border="0" style="border-collapse: collapse; margin-top: 10px;">
        <tr>
            <td><strong>Company:</strong></td>
            <td>{{ $invoice->company->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Invoice No:</strong></td>
            <td>{{ $invoice->invoice_number }}</td>
        </tr>
        <tr>
            <td><strong>Customer:</strong></td>
            <td>{{ $invoice->customer->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Invoice Date:</strong></td>
            <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Due Date:</strong></td>
            <td>{{ $invoice->remaining_due_date ? $invoice->remaining_due_date->format('d M Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Outstanding:</strong></td>
            <td>₹ {{ number_format($outstandingAmount, 2) }}</td>
        </tr>
    </table>

    <p style="margin-top: 14px;">
        Please follow up with the customer for collection.
    </p>
</body>
</html>
