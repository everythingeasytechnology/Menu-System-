<!DOCTYPE html>
<html>
<body style="margin:0;background:#fff8ee;color:#171717;font-family:Arial,sans-serif;line-height:1.55;">
    <div style="max-width:680px;margin:0 auto;padding:28px 18px;">
        <div style="background:#ffffff;border:1px solid #f0dcc2;border-radius:20px;padding:24px;box-shadow:0 16px 40px rgba(45,31,5,0.08);">
            <p style="margin:0 0 8px;color:#ff7a00;font-size:11px;font-weight:700;letter-spacing:.18em;text-transform:uppercase;">{{ $isUpdate ? 'Bill Updated' : 'Order Confirmation' }}</p>
            <h1 style="margin:0 0 10px;font-size:24px;line-height:1.2;">{{ $payload['business']['name'] }}</h1>
            <p style="margin:0 0 20px;font-size:14px;color:#5f574e;">
                @if($isUpdate)
                    New items were added to your order <strong>{{ $payload['order']['displayId'] }}</strong>. Your bill is updated and you can download the latest receipt anytime.
                @else
                    Your order <strong>{{ $payload['order']['displayId'] }}</strong> has been received. You can track status live and download the bill anytime.
                @endif
            </p>

            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;">
                <a href="{{ $payload['links']['track_url'] }}" style="display:inline-block;border-radius:12px;background:#ff7a00;color:#ffffff;padding:12px 16px;text-decoration:none;font-size:13px;font-weight:700;">Track Order</a>
                <a href="{{ $payload['links']['bill_print_url'] }}" style="display:inline-block;border-radius:12px;background:#fff4e4;color:#171717;border:1px solid #f0dcc2;padding:12px 16px;text-decoration:none;font-size:13px;font-weight:700;">Download Bill</a>
            </div>

            <div style="border:1px solid #f1e4d1;border-radius:16px;padding:16px;background:#fffdf8;">
                <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                    <div>
                        <div style="font-size:11px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#7b7065;">Bill No</div>
                        <div style="margin-top:4px;font-size:20px;font-weight:700;color:#ff7a00;">{{ $payload['receipt']['bill_no'] }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:11px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#7b7065;">Status</div>
                        <div style="margin-top:4px;font-size:14px;font-weight:700;color:#171717;">{{ $payload['receipt']['status_label'] }}</div>
                    </div>
                </div>

                <div style="margin-top:16px;border-top:1px dashed #d8c5ab;padding-top:14px;font-size:13px;color:#171717;">
                    <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:6px;"><span>Order No</span><span>{{ $payload['receipt']['order_number'] }}</span></div>
                    <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:6px;"><span>Date</span><span>{{ $payload['receipt']['date'] }}</span></div>
                    <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:6px;"><span>Location</span><span>{{ $payload['receipt']['location'] }}</span></div>
                    <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:6px;"><span>Customer</span><span>{{ $payload['receipt']['customer'] }}</span></div>
                    @if($payload['receipt']['phone'] && $payload['receipt']['phone'] !== 'N/A')
                        <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:6px;"><span>Phone</span><span>{{ $payload['receipt']['phone'] }}</span></div>
                    @endif
                </div>

                <div style="margin-top:14px;border-top:1px dashed #d8c5ab;padding-top:14px;">
                    <div style="font-size:11px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#7b7065;margin-bottom:10px;">Items</div>
                    @foreach($payload['receipt']['items'] as $item)
                        <div style="margin-bottom:12px;">
                            <div style="font-size:14px;font-weight:700;color:#171717;">{{ $item['name'] }}</div>
                            <div style="font-size:12px;color:#5f574e;margin-top:2px;">{{ number_format($item['unit_price'], 2) }} x {{ $item['quantity'] }}</div>
                            <div style="font-size:13px;font-weight:700;color:#171717;margin-top:4px;">Rs. {{ number_format($item['line_total'], 2) }}</div>
                            @if($item['special_instructions'])
                                <div style="font-size:12px;color:#ff7a00;margin-top:4px;">Note: {{ $item['special_instructions'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div style="margin-top:8px;border-top:1px dashed #d8c5ab;padding-top:14px;font-size:13px;color:#171717;">
                    <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:6px;"><span>Subtotal</span><span>Rs. {{ number_format($payload['receipt']['subtotal'], 2) }}</span></div>
                    @if($payload['receipt']['discount'] > 0)
                        <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:6px;color:#c2410c;">
                            <span>{{ $payload['receipt']['coupon_code'] ? 'Coupon ('.$payload['receipt']['coupon_code'].')' : 'Discount' }}</span>
                            <span>- Rs. {{ number_format($payload['receipt']['discount'], 2) }}</span>
                        </div>
                    @endif
                    @if($payload['receipt']['has_gst'])
                        <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:6px;"><span>Before GST</span><span>Rs. {{ number_format($payload['receipt']['taxable_after_discount'], 2) }}</span></div>
                        <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:6px;"><span>CGST ({{ number_format($payload['receipt']['cgst_rate'], 2) }}%)</span><span>Rs. {{ number_format($payload['receipt']['cgst_amount'], 2) }}</span></div>
                        <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:6px;"><span>SGST ({{ number_format($payload['receipt']['sgst_rate'], 2) }}%)</span><span>Rs. {{ number_format($payload['receipt']['sgst_amount'], 2) }}</span></div>
                        <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:6px;"><span>Total GST</span><span>Rs. {{ number_format($payload['receipt']['tax'], 2) }}</span></div>
                    @endif
                    <div style="display:flex;justify-content:space-between;gap:12px;margin-top:10px;padding-top:10px;border-top:1px solid #171717;font-size:16px;font-weight:700;">
                        <span>Total</span>
                        <span>Rs. {{ number_format($payload['receipt']['total'], 2) }}</span>
                    </div>
                </div>
            </div>

            <p style="margin:18px 0 0;font-size:12px;color:#5f574e;">
                Payment: {{ $payload['receipt']['payment_label'] }}
            </p>
        </div>
    </div>
</body>
</html>
