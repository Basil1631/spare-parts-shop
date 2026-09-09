<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Invoice {{ $bill->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        .muted { color: #555; }
        .right { text-align: right; }
    </style>
</head>
<body>
    @if (!empty($print))
        <p><button onclick="window.print()">Print</button></p>
    @endif
    <h1>Tax Invoice</h1>
    <table>
        <tr>
            <td>
                <strong>{{ $shop->shop_name }}</strong><br>
                {!! nl2br(e($shop->address)) !!}<br>
                TRN: {{ $shop->trn ?: '—' }}<br>
                {{ $shop->phone }}
            </td>
            <td>
                Invoice no: <strong>{{ $bill->number }}</strong><br>
                Date: {{ $bill->billed_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}<br>
                Payment: {{ $bill->payment_type->label() }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>Customer</strong><br>
                {{ $bill->garage_name ?: 'Walk-in customer' }}<br>
                {!! nl2br(e($bill->garage_address)) !!}<br>
                Phone: {{ $bill->garage_phone ?: '—' }}<br>
                TRN: {{ $bill->garage_trn ?: '—' }}
            </td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit price AED</th>
                <th>VAT %</th>
                <th>VAT AED</th>
                <th>Total AED</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($bill->items as $item)
            <tr>
                <td>{{ $item->sku }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->qty }}</td>
                <td class="right">{{ \App\Support\Money::fromFils($item->unit_price_fils) }}</td>
                <td>{{ $item->vat_rate }}</td>
                <td class="right">{{ \App\Support\Money::fromFils($item->line_vat_fils) }}</td>
                <td class="right">{{ \App\Support\Money::fromFils($item->line_total_fils) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p class="right">
        Taxable amount: AED {{ \App\Support\Money::fromFils($bill->subtotal_fils) }}<br>
        VAT: AED {{ \App\Support\Money::fromFils($bill->vat_fils) }}<br>
        <strong>Total AED {{ \App\Support\Money::fromFils($bill->total_fils) }}</strong>
    </p>
    <p class="muted">Amounts in United Arab Emirates dirhams (AED). Selling prices are exclusive of VAT.</p>
</body>
</html>
