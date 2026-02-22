<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estimate {{ $estimate->estimate_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .container { padding: 20px; }
        .header { display: table; width: 100%; margin-bottom: 20px; border-bottom: 3px solid #1a1c2e; padding-bottom: 15px; }
        .header-left { display: table-cell; width: 60%; vertical-align: top; }
        .header-right { display: table-cell; width: 40%; vertical-align: top; text-align: right; }
        .company-name { font-size: 24px; font-weight: bold; color: #1a1c2e; letter-spacing: 1px; }
        .company-tagline { font-size: 10px; color: #666; margin-top: 2px; }
        .company-details { font-size: 10px; color: #555; margin-top: 8px; }
        .title { text-align: center; margin: 15px 0; }
        .title h2 { font-size: 18px; color: #1a1c2e; display: inline-block; padding: 5px 30px; border: 2px solid #1a1c2e; letter-spacing: 2px; }
        .billing-section { display: table; width: 100%; margin-bottom: 15px; }
        .bill-to { display: table-cell; width: 55%; vertical-align: top; }
        .estimate-info { display: table-cell; width: 45%; vertical-align: top; }
        .section-title { font-size: 11px; font-weight: bold; color: #1a1c2e; text-transform: uppercase; margin-bottom: 5px; border-bottom: 1px solid #ddd; padding-bottom: 3px; }
        .info-table { width: 100%; }
        .info-table td { padding: 2px 5px; font-size: 11px; }
        .info-table .label { font-weight: bold; color: #555; width: 120px; }
        .items-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .items-table th { background: #1a1c2e; color: white; padding: 8px 6px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; }
        .items-table th:last-child, .items-table td:last-child { text-align: right; }
        .items-table th.center, .items-table td.center { text-align: center; }
        .items-table th.right, .items-table td.right { text-align: right; }
        .items-table td { padding: 7px 6px; border-bottom: 1px solid #eee; font-size: 11px; }
        .items-table tr:nth-child(even) { background: #f8f9fa; }
        .summary-section { display: table; width: 100%; margin-top: 10px; }
        .summary-left { display: table-cell; width: 55%; vertical-align: top; }
        .summary-right { display: table-cell; width: 45%; vertical-align: top; }
        .summary-table { width: 100%; }
        .summary-table td { padding: 4px 8px; font-size: 11px; }
        .summary-table .label { text-align: right; color: #555; }
        .summary-table .value { text-align: right; font-weight: bold; width: 120px; }
        .summary-table .total-row td { border-top: 2px solid #1a1c2e; font-size: 14px; color: #1a1c2e; padding-top: 8px; }
        .validity { background: #fff3cd; padding: 8px 12px; margin: 10px 0; font-size: 11px; border-left: 3px solid #ffc107; }
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
    <div class="container">
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

        <div class="title">
            <h2>ESTIMATE</h2>
        </div>

        <div class="billing-section">
            <div class="bill-to">
                <div class="section-title">Prepared For</div>
                <table class="info-table">
                    <tr><td class="label">Name:</td><td>{{ $estimate->customer->name }}</td></tr>
                    @if($estimate->customer->phone)<tr><td class="label">Phone:</td><td>{{ $estimate->customer->phone }}</td></tr>@endif
                    @if($estimate->customer->address)<tr><td class="label">Address:</td><td>{{ $estimate->customer->address }}</td></tr>@endif
                    @if($estimate->customer->gstin)<tr><td class="label">GSTIN:</td><td>{{ $estimate->customer->gstin }}</td></tr>@endif
                    @if($estimate->site)<tr><td class="label">Site:</td><td>{{ $estimate->site->site_name }}</td></tr>@endif
                </table>
            </div>
            <div class="estimate-info">
                <div class="section-title">Estimate Details</div>
                <table class="info-table">
                    <tr><td class="label">Estimate No:</td><td><strong>{{ $estimate->estimate_number }}</strong></td></tr>
                    <tr><td class="label">Date:</td><td>{{ $estimate->estimate_date->format('d-M-Y') }}</td></tr>
                    @if($estimate->valid_until)
                    <tr><td class="label">Valid Until:</td><td>{{ $estimate->valid_until->format('d-M-Y') }}</td></tr>
                    @endif
                    <tr><td class="label">Status:</td><td>{{ ucfirst($estimate->status) }}</td></tr>
                </table>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px;">S.No</th>
                    <th>Product / Description</th>
                    <th style="width: 60px;">HSN/SAC</th>
                    <th class="center" style="width: 40px;">Qty</th>
                    <th class="right" style="width: 80px;">Rate</th>
                    @if($estimate->is_gst)
                        <th class="center" style="width: 50px;">GST%</th>
                        <th class="right" style="width: 80px;">GST Amt</th>
                    @endif
                    <th class="right" style="width: 90px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($estimate->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $item->product->name }}
                        @if($item->product->brand) <span style="color: #666;">- {{ $item->product->brand }}</span>@endif
                        @if($item->product->model_number) <span style="color: #666;">({{ $item->product->model_number }})</span>@endif
                    </td>
                    <td>{{ $item->product->hsn_sac ?? '-' }}</td>
                    <td class="center">{{ $item->qty }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                    @if($estimate->is_gst)
                        <td class="center">{{ $item->gst_percent }}%</td>
                        <td class="right">{{ number_format(($item->qty * $item->unit_price) * ($item->gst_percent / 100), 2) }}</td>
                    @endif
                    <td class="right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-section">
            <div class="summary-left">
                @if($estimate->valid_until)
                <div class="validity">
                    <strong>This estimate is valid until {{ $estimate->valid_until->format('d-M-Y') }}</strong>
                </div>
                @endif
                @if($estimate->notes)
                    <div style="margin-top: 10px; font-size: 10px;">
                        <strong>Notes:</strong> {{ $estimate->notes }}
                    </div>
                @endif
            </div>
            <div class="summary-right">
                <table class="summary-table">
                    <tr><td class="label">Subtotal:</td><td class="value">{{ number_format($estimate->subtotal, 2) }}</td></tr>
                    @if($estimate->is_gst)
                        <tr><td class="label">CGST:</td><td class="value">{{ number_format($estimate->gst_amount / 2, 2) }}</td></tr>
                        <tr><td class="label">SGST:</td><td class="value">{{ number_format($estimate->gst_amount / 2, 2) }}</td></tr>
                    @endif
                    @if($estimate->discount > 0)
                        <tr><td class="label">Discount:</td><td class="value">-{{ number_format($estimate->discount, 2) }}</td></tr>
                    @endif
                    <tr class="total-row">
                        <td class="label"><strong>GRAND TOTAL:</strong></td>
                        <td class="value"><strong>₹ {{ number_format($estimate->total, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="signature-section">
            <div class="sig-left">
                <div class="sig-line"></div>
                <div class="sig-label">Customer Acceptance</div>
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
                1. This is an estimate and not a confirmed order.<br>
                2. Prices are subject to change without prior notice.<br>
                3. Taxes are applicable as per current rates.<br>
                4. Warranty terms apply as mentioned for individual products.
            </div>
        </div>
    </div>
</body>
</html>
