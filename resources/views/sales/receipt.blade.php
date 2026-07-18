@php
    $subtotal = $sale->items->sum(fn ($i) => (float) $i->sale_price * (float) $i->qty);
    $discount = (float) ($sale->discount ?? 0);
    $total = (float) $sale->total_amount;
    $paid = (float) $sale->advance_paid;
    $remaining = max(0, $total - $paid);
    $shopName = config('app.name', 'Dulhan House');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Receipt #{{ $sale->id }}</title>
    <style>
        /* Cobra 80mm thermal printer receipt */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: 80mm auto; margin: 0; }
        html, body { background: #fff; }
        body {
            width: 80mm;
            margin: 0 auto;
            padding: 4mm 3mm 6mm;
            font-family: "Consolas", "Courier New", monospace;
            font-size: 12px;
            line-height: 1.35;
            color: #000;
        }
        .center { text-align: center; }
        .bold { font-weight: 700; }
        .shop { font-size: 18px; font-weight: 800; letter-spacing: .5px; }
        .sub { font-size: 11px; }
        .hr { border-top: 1px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; gap: 6px; }
        .row .l { flex: 0 0 auto; }
        .row .v { text-align: right; flex: 1 1 auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { font-size: 11px; padding: 2px 0; vertical-align: top; }
        th { border-bottom: 1px solid #000; text-align: left; }
        td.amt, th.amt { text-align: right; white-space: nowrap; }
        .muted { font-size: 10px; }
        .big { font-size: 14px; font-weight: 800; }
        .foot { margin-top: 10px; font-size: 10px; }
        .sign { margin-top: 22px; }
        .sign .line { border-top: 1px solid #000; width: 60%; margin-top: 16px; padding-top: 2px; font-size: 10px; }
        @media screen {
            body { box-shadow: 0 0 0 1px #ddd; margin-top: 12px; margin-bottom: 12px; }
            .noprint { text-align: center; margin: 10px 0; }
            .noprint button { font-family: sans-serif; padding: 8px 16px; font-size: 13px; cursor: pointer; }
        }
        @media print { .noprint { display: none !important; } }
    </style>
</head>
<body>
    <div class="noprint">
        <button onclick="window.print()">🖨 Print</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="center">
        <div class="shop">{{ $shopName }}</div>
        <div class="sub">Bridal &amp; Sherwani</div>
        <div class="sub">Sale Receipt</div>
    </div>

    <div class="hr"></div>

    <div class="row"><span class="l">Bill Ref</span><span class="v bold">{{ $sale->bill_ref ?: '#' . $sale->id }}</span></div>
    <div class="row"><span class="l">Sale #</span><span class="v">{{ $sale->id }}</span></div>
    <div class="row"><span class="l">Date</span><span class="v">{{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') : now()->format('d/m/Y') }}</span></div>
    <div class="row"><span class="l">Printed</span><span class="v">{{ now()->format('d/m/Y g:i A') }}</span></div>
    <div class="row"><span class="l">Staff</span><span class="v">{{ $sale->employee?->name ?? '—' }}</span></div>

    <div class="hr"></div>

    <div class="row"><span class="l">Customer</span><span class="v bold">{{ $sale->customer_name }}</span></div>
    @if ($sale->customer_phone1)
        <div class="row"><span class="l">Phone</span><span class="v">{{ $sale->customer_phone1 }}</span></div>
    @endif

    <div class="hr"></div>

    <table>
        <thead>
            <tr><th>Item</th><th class="amt">Qty</th><th class="amt">Amount</th></tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $it)
                <tr>
                    <td>
                        {{ $it->product_code ? $it->product_code . ' — ' : '' }}{{ $it->product_name }}
                        <div class="muted">@ Rs. {{ number_format((float) $it->sale_price, 0) }}</div>
                    </td>
                    <td class="amt">{{ rtrim(rtrim(number_format((float) $it->qty, 2), '0'), '.') }}</td>
                    <td class="amt">{{ number_format((float) $it->sale_price * (float) $it->qty, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="hr"></div>

    <div class="row"><span class="l">Subtotal</span><span class="v">Rs. {{ number_format($subtotal, 0) }}</span></div>
    @if ($discount > 0)
        <div class="row"><span class="l">Discount</span><span class="v">- Rs. {{ number_format($discount, 0) }}</span></div>
    @endif
    <div class="row big"><span class="l">TOTAL</span><span class="v">Rs. {{ number_format($total, 0) }}</span></div>
    <div class="row"><span class="l">Paid</span><span class="v">Rs. {{ number_format($paid, 0) }}</span></div>
    <div class="row bold"><span class="l">Balance</span><span class="v">Rs. {{ number_format($remaining, 0) }}</span></div>

    <div class="sign">
        <div class="line">Customer Signature</div>
    </div>

    <div class="foot center">
        <div class="bold" style="margin-top:6px;">Thank you for your purchase! — {{ $shopName }}</div>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>
</body>
</html>
