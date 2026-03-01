<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation_template->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .container { padding: 20px; }
        .header { display: table; width: 100%; margin-bottom: 20px; border-bottom: 3px solid #1a1c2e; padding-bottom: 15px; }
        .header-left { display: table-cell; width: 60%; vertical-align: top; }
        .header-right { display: table-cell; width: 40%; vertical-align: top; text-align: right; }
        .company-name { font-size: 24px; font-weight: bold; color: #1a1c2e; letter-spacing: 1px; }
        .company-details { font-size: 10px; color: #555; margin-top: 8px; }
        .title { text-align: center; margin: 15px 0; }
        .title h2 { font-size: 18px; color: #1a1c2e; display: inline-block; padding: 5px 30px; border: 2px solid #1a1c2e; letter-spacing: 2px; }
        .items-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .items-table th { background: #1a1c2e; color: white; padding: 8px 6px; font-size: 10px; text-transform: uppercase; text-align: left; }
        .items-table th:last-child, .items-table td:last-child { text-align: right; }
        .items-table th.center, .items-table td.center { text-align: center; }
        .items-table td { padding: 7px 6px; border-bottom: 1px solid #eee; font-size: 11px; }
        .items-table tr:nth-child(even) { background: #f8f9fa; }
        .total-row { font-weight: bold; font-size: 14px; color: #1a1c2e; border-top: 2px solid #1a1c2e; padding-top: 8px; }
        .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <div class="company-name">{{ $company->name ?? 'Company' }}</div>
                <div class="company-details">
                    @if($company && $company->address){{ $company->address }}<br>@endif
                    @if($company && $company->phone)Phone: {{ $company->phone }}@endif
                    @if($company && $company->email) | Email: {{ $company->email }}@endif
                </div>
            </div>
            @if($company && $company->gstin)
            <div class="header-right"><div style="font-size: 11px; font-weight: bold;">GSTIN: {{ $company->gstin }}</div></div>
            @endif
        </div>

        <div class="title">
            <h2>QUOTATION – {{ strtoupper($quotation_template->name) }}</h2>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px;">S.No</th>
                    <th>Item / Description</th>
                    <th class="center" style="width: 50px;">Qty</th>
                    <th style="width: 90px;" class="right">Rate</th>
                    <th style="width: 90px;" class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation_template->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->display_name }}</td>
                    <td class="center">{{ $item->qty }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">{{ number_format($item->qty * $item->unit_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="right">Total</td>
                    <td class="right">₹ {{ number_format($quotation_template->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            This is a quotation template. For a formal estimate, create an estimate from this template and send to the customer.
        </div>
    </div>
</body>
</html>
