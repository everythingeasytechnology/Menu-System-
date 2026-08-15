<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $payload['receipt']['bill_no'] ?? 'Bill' }} - Bill Receipt</title>

    @php $brandLogoUrl = app(\App\Services\MailBrandingService::class)->logoUrl(); @endphp
    @if($brandLogoUrl)
        <link rel="icon" type="image/png" href="{{ $brandLogoUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
    @endif

    <style>
        :root {
            color-scheme: light;
        }

        body {
            margin: 0;
            background: #f4efe6;
            color: #171717;
            font-family: "Courier New", Courier, monospace;
        }

        .shell {
            max-width: 920px;
            margin: 0 auto;
            padding: 24px 16px 40px;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid #d5cbbb;
            background: #fff9ef;
            color: #171717;
        }

        .btn.primary {
            background: #ff7a00;
            border-color: #ff7a00;
            color: #fff;
        }

        .receipt {
            max-width: 340px;
            margin: 0 auto;
            background: #fffdf7;
            border: 1px solid #e7dcc8;
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(38, 27, 6, 0.12);
            padding: 18px 16px 20px;
        }

        .center {
            text-align: center;
        }

        .brand {
            font-size: 18px;
            font-weight: 700;
        }

        .muted {
            color: #685f56;
            font-size: 12px;
            line-height: 1.5;
        }

        .divider {
            border-top: 1px dashed #95846d;
            margin: 12px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin: 6px 0;
            font-size: 12px;
        }

        .row span:last-child {
            text-align: right;
            white-space: nowrap;
        }

        .section {
            margin: 14px 0 8px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .item {
            margin: 10px 0;
        }

        .item-name {
            font-size: 12px;
            font-weight: 700;
            white-space: pre-wrap;
        }

        .item-note {
            margin-top: 4px;
            color: #685f56;
            font-size: 11px;
        }

        .total {
            border-top: 1px solid #171717;
            margin-top: 8px;
            padding-top: 8px;
            font-size: 14px;
            font-weight: 700;
        }

        @media print {
            body {
                background: #fff;
            }

            .shell {
                padding: 0;
            }

            .actions {
                display: none;
            }

            .receipt {
                box-shadow: none;
                border: 0;
                border-radius: 0;
                max-width: 76mm;
                padding: 8px 6px 12px;
            }
        }
    </style>
</head>
<body @if($autoPrint) onload="window.print()" @endif>
    <div class="shell">
        <div class="actions">
            <a href="{{ $payload['links']['track_url'] }}" class="btn">Track Order</a>
            <button type="button" onclick="window.print()" class="btn primary">Download Bill</button>
        </div>

        <article class="receipt">
            <header class="center">
                <div class="brand">{{ $payload['business']['name'] }}</div>
                @if($payload['business']['address'])
                    <div class="muted">{{ $payload['business']['address'] }}</div>
                @endif
                @if($payload['business']['pincode'])
                    <div class="muted">PIN: {{ $payload['business']['pincode'] }}</div>
                @endif
                @if($payload['receipt']['has_gst'] && $payload['business']['gst_no'])
                    <div class="muted">GSTIN: {{ $payload['business']['gst_no'] }}</div>
                @endif
            </header>

            <div class="divider"></div>

            <div class="row"><span>Bill No:</span><span>{{ $payload['receipt']['bill_no'] }}</span></div>
            <div class="row"><span>Order No:</span><span>{{ $payload['receipt']['order_number'] }}</span></div>
            <div class="row"><span>Date:</span><span>{{ $payload['receipt']['date'] }}</span></div>
            <div class="row"><span>Channel:</span><span>{{ $payload['receipt']['channel'] }}</span></div>
            <div class="row"><span>Location:</span><span>{{ $payload['receipt']['location'] }}</span></div>
            <div class="row"><span>Customer:</span><span>{{ $payload['receipt']['customer'] }}</span></div>
            @if($payload['receipt']['phone'] && $payload['receipt']['phone'] !== 'N/A')
                <div class="row"><span>Phone:</span><span>{{ $payload['receipt']['phone'] }}</span></div>
            @endif
            @if($payload['receipt']['email'])
                <div class="row"><span>Email:</span><span>{{ $payload['receipt']['email'] }}</span></div>
            @endif

            <div class="divider"></div>
            <div class="section">Item Details</div>

            @foreach($payload['receipt']['items'] as $index => $item)
                <div class="item">
                    <div class="item-name">{{ $index + 1 }}. {{ $item['name'] }}</div>
                    <div class="row">
                        <span>{{ number_format($item['unit_price'], 2) }} x {{ $item['quantity'] }}</span>
                        <span>Rs. {{ number_format($item['line_subtotal'], 2) }}</span>
                    </div>
                    @if($item['discount'] > 0)
                        <div class="row">
                            <span>Item discount</span>
                            <span>- Rs. {{ number_format($item['discount'], 2) }}</span>
                        </div>
                    @endif
                    @if($item['tax'] > 0)
                        <div class="row">
                            <span>GST on item</span>
                            <span>Rs. {{ number_format($item['tax'], 2) }}</span>
                        </div>
                    @endif
                    <div class="row">
                        <span>Item total</span>
                        <span>Rs. {{ number_format($item['line_total'], 2) }}</span>
                    </div>
                    @if($item['special_instructions'])
                        <div class="item-note">Note: {{ $item['special_instructions'] }}</div>
                    @endif
                </div>
            @endforeach

            <div class="divider"></div>
            <div class="section">{{ $payload['receipt']['has_gst'] ? 'GST Bill Summary' : 'Bill Summary' }}</div>

            @if($payload['receipt']['has_gst'])
                <div class="row"><span>Before GST:</span><span>Rs. {{ number_format($payload['receipt']['subtotal'], 2) }}</span></div>
                @if($payload['receipt']['discount'] > 0)
                    <div class="row">
                        <span>{{ $payload['receipt']['coupon_code'] ? 'Coupon ('.$payload['receipt']['coupon_code'].'):' : 'Discount:' }}</span>
                        <span>- Rs. {{ number_format($payload['receipt']['discount'], 2) }}</span>
                    </div>
                    <div class="row"><span>Before GST after discount:</span><span>Rs. {{ number_format($payload['receipt']['taxable_after_discount'], 2) }}</span></div>
                @endif
                <div class="row"><span>CGST ({{ number_format($payload['receipt']['cgst_rate'], 2) }}%):</span><span>Rs. {{ number_format($payload['receipt']['cgst_amount'], 2) }}</span></div>
                <div class="row"><span>SGST ({{ number_format($payload['receipt']['sgst_rate'], 2) }}%):</span><span>Rs. {{ number_format($payload['receipt']['sgst_amount'], 2) }}</span></div>
                <div class="row"><span>Total GST:</span><span>Rs. {{ number_format($payload['receipt']['tax'], 2) }}</span></div>
                <div class="row total"><span>Total after GST:</span><span>Rs. {{ number_format($payload['receipt']['total'], 2) }}</span></div>
            @else
                <div class="row"><span>Subtotal:</span><span>Rs. {{ number_format($payload['receipt']['subtotal'], 2) }}</span></div>
                @if($payload['receipt']['discount'] > 0)
                    <div class="row">
                        <span>{{ $payload['receipt']['coupon_code'] ? 'Coupon ('.$payload['receipt']['coupon_code'].'):' : 'Discount:' }}</span>
                        <span>- Rs. {{ number_format($payload['receipt']['discount'], 2) }}</span>
                    </div>
                @endif
                <div class="row total"><span>Total:</span><span>Rs. {{ number_format($payload['receipt']['total'], 2) }}</span></div>
            @endif

            <div class="row"><span>Payment:</span><span>{{ $payload['receipt']['payment_label'] }}</span></div>
            <div class="row"><span>Order status:</span><span>{{ $payload['receipt']['status_label'] }}</span></div>

            @if($payload['receipt']['note'])
                <div class="divider"></div>
                <div class="item-note">Instructions: {{ $payload['receipt']['note'] }}</div>
            @endif

            <div class="divider"></div>
            <div class="center">Thank you</div>
        </article>
    </div>
</body>
</html>
