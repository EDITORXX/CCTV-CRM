<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Advance Receipt {{ $customerAdvance->receipt_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.5; }

        .receipt-container { padding: 20px; }

        .header { display: table; width: 100%; margin-bottom: 20px; border-bottom: 3px solid #1a1c2e; padding-bottom: 15px; }
        .header-left { display: table-cell; width: 60%; vertical-align: top; }
        .header-right { display: table-cell; width: 40%; vertical-align: top; text-align: right; }
        .company-name { font-size: 24px; font-weight: bold; color: #1a1c2e; letter-spacing: 1px; }
        .company-tagline { font-size: 10px; color: #666; margin-top: 2px; }
        .company-details { font-size: 10px; color: #555; margin-top: 8px; }

        .receipt-title { text-align: center; margin: 15px 0; }
        .receipt-title h2 {
            font-size: 18px; color: #1a1c2e;
            display: inline-block; padding: 5px 30px;
            border: 2px solid #1a1c2e; letter-spacing: 2px;
        }

        .billing-section { display: table; width: 100%; margin-bottom: 15px; }
        .bill-to { display: table-cell; width: 55%; vertical-align: top; }
        .receipt-info { display: table-cell; width: 45%; vertical-align: top; }
        .section-title { font-size: 11px; font-weight: bold; color: #1a1c2e; text-transform: uppercase; margin-bottom: 5px; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
        .info-table { width: 100%; }
        .info-table td { padding: 2px 5px; font-size: 11px; }
        .info-table .label { font-weight: bold; color: #555; width: 120px; }

        .amount-box { background: #f0f0f0; border: 2px solid #1a1c2e; padding: 15px 20px; margin: 20px 0; text-align: center; }
        .amount-box .label { font-size: 11px; color: #555; }
        .amount-box .value { font-size: 22px; font-weight: bold; color: #1a1c2e; margin-top: 5px; }

        .amount-words { background: #f8f9fa; padding: 8px 12px; margin: 10px 0; font-size: 11px; border-left: 3px solid #1a1c2e; }

        .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; }
        .terms { font-size: 9px; color: #666; }
        .signature-section { display: table; width: 100%; margin-top: 40px; }
        .sig-left { display: table-cell; width: 50%; }
        .sig-right { display: table-cell; width: 50%; text-align: right; }
        .sig-line { border-top: 1px solid #333; display: inline-block; width: 150px; margin-top: 40px; }
        .sig-label { font-size: 10px; color: #555; margin-top: 3px; }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <div class="header-left">
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-tagline">Advance Receipt</div>
                <div class="company-details">
                    @if($company->address){{ $company->address }}<br>@endif
                    @if($company->phone)Phone: {{ $company->phone }}@endif
                    @if($company->email) | Email: {{ $company->email }}@endif
                </div>
            </div>
            <div class="header-right">
                @if($company->gstin)
                    <div style="font-size: 11px; font-weight: bold;">GSTIN: {{ $company->gstin }}</div>
                @endif
            </div>
        </div>

        <div class="receipt-title">
            <h2>ADVANCE RECEIPT</h2>
        </div>

        <div class="billing-section">
            <div class="bill-to">
                <div class="section-title">Received From</div>
                <table class="info-table">
                    <tr><td class="label">Name:</td><td><strong>{{ $customerAdvance->customer->name }}</strong></td></tr>
                    @if($customerAdvance->customer->phone)<tr><td class="label">Phone:</td><td>{{ $customerAdvance->customer->phone }}</td></tr>@endif
                    @if($customerAdvance->customer->address)<tr><td class="label">Address:</td><td>{{ $customerAdvance->customer->address }}</td></tr>@endif
                    @if($customerAdvance->customer->gstin)<tr><td class="label">GSTIN:</td><td>{{ $customerAdvance->customer->gstin }}</td></tr>@endif
                </table>
            </div>
            <div class="receipt-info">
                <div class="section-title">Receipt Details</div>
                <table class="info-table">
                    <tr><td class="label">Receipt No:</td><td><strong>{{ $customerAdvance->receipt_number }}</strong></td></tr>
                    <tr><td class="label">Date:</td><td>{{ $customerAdvance->payment_date->format('d-M-Y') }}</td></tr>
                    <tr><td class="label">Payment Method:</td><td>{{ ucfirst(str_replace('_', ' ', $customerAdvance->payment_method)) }}</td></tr>
                    @if($customerAdvance->reference_number)<tr><td class="label">Reference:</td><td>{{ $customerAdvance->reference_number }}</td></tr>@endif
                </table>
            </div>
        </div>

        <div class="amount-box">
            <div class="label">Amount Received (Advance)</div>
            <div class="value">₹ {{ number_format($customerAdvance->amount, 2) }}</div>
        </div>

        <div class="amount-words">
            <strong>Amount in Words:</strong><br>
            Rupees {{ ucwords(strtolower(\App\Helpers\NumberToWords::convert($customerAdvance->amount))) }} Only
        </div>

        @if($customerAdvance->notes)
            <div style="margin-top: 10px; font-size: 10px;">
                <strong>Notes:</strong> {{ $customerAdvance->notes }}
            </div>
        @endif

        <div class="footer">
            <div class="terms">
                This is a receipt for advance payment received. The amount will be adjusted against future invoice(s) as per agreement.
            </div>
        </div>

        <div class="signature-section">
            <div class="sig-left">
                <div class="sig-line"></div>
                <div class="sig-label">Customer Signature</div>
            </div>
            <div class="sig-right">
                <div class="sig-line"></div>
                <div class="sig-label">For {{ $company->name }}</div>
                <div class="sig-label">Authorized Signatory</div>
            </div>
        </div>
    </div>
</body>
</html>
