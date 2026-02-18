<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.5; }

        .invoice-container { padding: 20px; }

        .header { display: table; width: 100%; margin-bottom: 20px; border-bottom: 3px solid #1a1c2e; padding-bottom: 15px; }
        .header-left { display: table-cell; width: 60%; vertical-align: top; }
        .header-right { display: table-cell; width: 40%; vertical-align: top; text-align: right; }
        .company-name { font-size: 24px; font-weight: bold; color: #1a1c2e; letter-spacing: 1px; }
        .company-tagline { font-size: 10px; color: #666; margin-top: 2px; }
        .company-details { font-size: 10px; color: #555; margin-top: 8px; }

        .invoice-title { text-align: center; margin: 15px 0; }
        .invoice-title h2 {
            font-size: 18px; color: #1a1c2e;
            display: inline-block; padding: 5px 30px;
            border: 2px solid #1a1c2e; letter-spacing: 2px;
        }
        .gst-label { font-size: 10px; color: #666; margin-top: 3px; }

        .billing-section { display: table; width: 100%; margin-bottom: 15px; }
        .bill-to { display: table-cell; width: 55%; vertical-align: top; }
        .invoice-info { display: table-cell; width: 45%; vertical-align: top; }
        .section-title { font-size: 11px; font-weight: bold; color: #1a1c2e; text-transform: uppercase; margin-bottom: 5px; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
        .info-table { width: 100%; }
        .info-table td { padding: 2px 5px; font-size: 11px; }
        .info-table .label { font-weight: bold; color: #555; width: 120px; }

        .items-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .items-table th {
            background: #1a1c2e; color: white; padding: 8px 6px;
            font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;
            text-align: left;
        }
        .items-table th:last-child, .items-table td:last-child { text-align: right; }
        .items-table th.center, .items-table td.center { text-align: center; }
        .items-table th.right, .items-table td.right { text-align: right; }
        .items-table td { padding: 7px 6px; border-bottom: 1px solid #eee; font-size: 11px; }
        .items-table tr:nth-child(even) { background: #f8f9fa; }
        .serial-text { font-size: 9px; color: #666; margin-top: 2px; }

        .summary-section { display: table; width: 100%; margin-top: 10px; }
        .summary-left { display: table-cell; width: 55%; vertical-align: top; }
        .summary-right { display: table-cell; width: 45%; vertical-align: top; }
        .summary-table { width: 100%; }
        .summary-table td { padding: 4px 8px; font-size: 11px; }
        .summary-table .label { text-align: right; color: #555; }
        .summary-table .value { text-align: right; font-weight: bold; width: 120px; }
        .summary-table .total-row td { border-top: 2px solid #1a1c2e; font-size: 14px; color: #1a1c2e; padding-top: 8px; }

        .amount-words { background: #f0f0f0; padding: 8px 12px; margin: 10px 0; font-size: 11px; border-left: 3px solid #1a1c2e; }

        .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; }
        .terms { font-size: 9px; color: #666; }
        .signature-section { display: table; width: 100%; margin-top: 40px; }
        .sig-left { display: table-cell; width: 50%; }
        .sig-right { display: table-cell; width: 50%; text-align: right; }
        .sig-line { border-top: 1px solid #333; display: inline-block; width: 150px; margin-top: 40px; }
        .sig-label { font-size: 10px; color: #555; margin-top: 3px; }

        .watermark { position: fixed; top: 40%; left: 20%; font-size: 80px; color: rgba(0,0,0,0.05); transform: rotate(-30deg); z-index: -1; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Company Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-tagline">Complete CCTV & Security Solutions</div>
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

        <!-- Invoice Title -->
        <div class="invoice-title">
            <h2>{{ $invoice->is_gst ? 'TAX INVOICE' : 'INVOICE' }}</h2>
            @if(!$invoice->is_gst)
                <div class="gst-label">(Without GST)</div>
            @endif
        </div>

        <!-- Billing Section -->
        <div class="billing-section">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                <table class="info-table">
                    <tr><td class="label">Name:</td><td>{{ $invoice->customer->name }}</td></tr>
                    @if($invoice->customer->phone)<tr><td class="label">Phone:</td><td>{{ $invoice->customer->phone }}</td></tr>@endif
                    @if($invoice->customer->address)<tr><td class="label">Address:</td><td>{{ $invoice->customer->address }}</td></tr>@endif
                    @if($invoice->customer->gstin)<tr><td class="label">GSTIN:</td><td>{{ $invoice->customer->gstin }}</td></tr>@endif
                    @if($invoice->site)<tr><td class="label">Site:</td><td>{{ $invoice->site->site_name }}</td></tr>@endif
                </table>
            </div>
            <div class="invoice-info">
                <div class="section-title">Invoice Details</div>
                <table class="info-table">
                    <tr><td class="label">Invoice No:</td><td><strong>{{ $invoice->invoice_number }}</strong></td></tr>
                    <tr><td class="label">Date:</td><td>{{ $invoice->invoice_date->format('d-M-Y') }}</td></tr>
                    <tr><td class="label">Status:</td><td>{{ ucfirst($invoice->status) }}</td></tr>
                </table>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px;">S.No</th>
                    <th>Product / Description</th>
                    <th style="width: 60px;">HSN/SAC</th>
                    <th class="center" style="width: 40px;">Qty</th>
                    <th class="right" style="width: 80px;">Rate</th>
                    @if($invoice->is_gst)
                        <th class="center" style="width: 50px;">GST%</th>
                        <th class="right" style="width: 80px;">GST Amt</th>
                    @endif
                    <th class="right" style="width: 90px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $item->product->name }}
                        @if($item->product->brand) <span style="color: #666;">- {{ $item->product->brand }}</span>@endif
                        @if($item->product->model_number) <span style="color: #666;">({{ $item->product->model_number }})</span>@endif
                        @if($item->serialNumbers->count())
                            <div class="serial-text">S/N: {{ $item->serialNumbers->pluck('serial_number')->implode(', ') }}</div>
                        @endif
                    </td>
                    <td>{{ $item->product->hsn_sac ?? '-' }}</td>
                    <td class="center">{{ $item->qty }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                    @if($invoice->is_gst)
                        <td class="center">{{ $item->gst_percent }}%</td>
                        <td class="right">{{ number_format(($item->qty * $item->unit_price) * ($item->gst_percent / 100), 2) }}</td>
                    @endif
                    <td class="right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary-section">
            <div class="summary-left">
                <div class="amount-words">
                    <strong>Amount in Words:</strong><br>
                    Rupees {{ ucwords(strtolower(\App\Helpers\NumberToWords::convert($invoice->total))) }} Only
                </div>

                @if($invoice->notes)
                    <div style="margin-top: 10px; font-size: 10px;">
                        <strong>Notes:</strong> {{ $invoice->notes }}
                    </div>
                @endif
            </div>
            <div class="summary-right">
                <table class="summary-table">
                    <tr><td class="label">Subtotal:</td><td class="value">{{ number_format($invoice->subtotal, 2) }}</td></tr>
                    @if($invoice->is_gst)
                        <tr><td class="label">CGST:</td><td class="value">{{ number_format($invoice->gst_amount / 2, 2) }}</td></tr>
                        <tr><td class="label">SGST:</td><td class="value">{{ number_format($invoice->gst_amount / 2, 2) }}</td></tr>
                    @endif
                    @if($invoice->discount > 0)
                        <tr><td class="label">Discount:</td><td class="value">-{{ number_format($invoice->discount, 2) }}</td></tr>
                    @endif
                    <tr class="total-row">
                        <td class="label"><strong>GRAND TOTAL:</strong></td>
                        <td class="value"><strong>₹ {{ number_format($invoice->total, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Signature & Footer -->
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

        <div class="footer">
            <div class="terms">
                <strong>Terms & Conditions:</strong><br>
                1. Goods once sold will not be taken back.<br>
                2. Warranty is subject to the terms mentioned in the warranty card.<br>
                3. Payment is due within 15 days from the date of invoice.<br>
                4. All disputes are subject to local jurisdiction.
            </div>
        </div>
    </div>
</body>
</html>
