<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Credit Note {{ $note->number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Tax Credit Note</h1>
    <p>
        <strong>{{ $shop->shop_name }}</strong><br>
        {!! nl2br(e($shop->address)) !!}<br>
        Supplier TRN: {{ $shop->trn ?: '—' }}
    </p>
    <p>
        Credit note no: <strong>{{ $note->number }}</strong><br>
        Date: {{ $note->created_at->timezone(config('app.timezone'))->format('d/m/Y') }}<br>
        Original tax invoice: {{ $note->bill->number }} dated {{ $note->bill->billed_at->timezone(config('app.timezone'))->format('d/m/Y') }}<br>
        Customer: {{ $note->bill->garage_name ?: 'Walk-in customer' }}<br>
        Customer TRN: {{ $note->bill->garage_trn ?: '—' }}<br>
        Reason: {{ $note->reason }}
    </p>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Qty</th>
                <th>Net AED</th>
                <th>VAT AED</th>
                <th>Total AED</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($note->items as $item)
            <tr>
                <td>{{ $item->billItem->name ?? '' }} ({{ $item->billItem->sku ?? '' }})</td>
                <td>{{ $item->qty }}</td>
                <td class="right">{{ \App\Support\Money::fromFils($item->line_subtotal_fils) }}</td>
                <td class="right">{{ \App\Support\Money::fromFils($item->line_vat_fils) }}</td>
                <td class="right">{{ \App\Support\Money::fromFils($item->line_total_fils) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p class="right">
        Original invoice total: AED {{ \App\Support\Money::fromFils($note->bill->total_fils) }}<br>
        This credit: AED {{ \App\Support\Money::fromFils($note->total_fils) }} (VAT {{ \App\Support\Money::fromFils($note->vat_fils) }})<br>
        Corrected remaining billed value: AED {{ \App\Support\Money::fromFils(max(0, $note->bill->total_fils - $note->bill->credited_fils)) }}
    </p>
    <p>All amounts in AED.</p>
</body>
</html>
