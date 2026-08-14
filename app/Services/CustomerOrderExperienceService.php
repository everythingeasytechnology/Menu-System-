<?php

namespace App\Services;

use App\Mail\CustomerOrderReceiptMail;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class CustomerOrderExperienceService
{
    public function __construct(
        private readonly MailSettingsService $mailSettingsService,
        private readonly OwnerDashboardService $ownerDashboardService,
    ) {
    }

    public function payload(Order $order): array
    {
        $order->loadMissing([
            'business.businessSetting',
            'items',
            'payments',
            'restaurantTable',
            'room',
            'servicePoint',
            'user',
            'coupon',
        ]);

        $business = $order->business;
        $settings = $business
            ? $this->ownerDashboardService->settingsFor($business)
            : new BusinessSetting([
                'brand_name' => config('app.name'),
                'gst_enabled' => false,
                'cgst' => 0,
                'sgst' => 0,
            ]);
        $formattedOrder = $this->ownerDashboardService->formatOrder($order);

        return [
            'business' => $this->businessPayload($business, $settings),
            'order' => $formattedOrder,
            'receipt' => $this->receiptPayload($order, $settings, $formattedOrder),
            'links' => $this->links($order),
        ];
    }

    public function links(Order $order): array
    {
        return [
            'track_url' => URL::signedRoute('customer.orders.track', ['orderNumber' => $order->order_number]),
            'status_url' => URL::signedRoute('customer.orders.track.data', ['orderNumber' => $order->order_number]),
            'bill_url' => URL::signedRoute('customer.orders.bill', ['orderNumber' => $order->order_number]),
            'bill_print_url' => URL::signedRoute('customer.orders.bill', [
                'orderNumber' => $order->order_number,
                'print' => 1,
            ]),
        ];
    }

    public function sendConfirmationIfPossible(Order $order): bool
    {
        if (! filled($order->customer_email)) {
            return false;
        }

        if (! $this->mailSettingsService->apply()) {
            return false;
        }

        try {
            Mail::to($order->customer_email)->send(new CustomerOrderReceiptMail($order, $this->payload($order)));

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }

    private function businessPayload(?Business $business, BusinessSetting $settings): array
    {
        return [
            'name' => $settings->brand_name ?: $business?->name ?: config('app.name'),
            'address' => $settings->address ?: $business?->address,
            'pincode' => $settings->pincode,
            'phone' => $business?->phone,
            'email' => $settings->business_email ?: $business?->email,
            'gst_no' => $settings->gst_no,
            'gst_enabled' => (bool) $settings->gst_enabled,
            'cgst' => (float) ($settings->cgst ?? 0),
            'sgst' => (float) ($settings->sgst ?? 0),
        ];
    }

    private function receiptPayload(Order $order, BusinessSetting $settings, array $formattedOrder): array
    {
        $subtotal = (float) $order->subtotal;
        $discount = (float) $order->discount;
        $hasGst = (bool) $settings->gst_enabled;
        $tax = $hasGst ? (float) $order->tax : 0.0;
        $taxableAfterDiscount = round(max(0, $subtotal - $discount), 2);
        $total = $hasGst ? (float) $order->total : $taxableAfterDiscount;
        $cgstRate = (float) ($settings->cgst ?? 0);
        $sgstRate = (float) ($settings->sgst ?? 0);
        $totalTaxRate = $cgstRate + $sgstRate;
        $cgstAmount = $hasGst
            ? round($totalTaxRate > 0 ? $tax * ($cgstRate / $totalTaxRate) : $tax / 2, 2)
            : 0.0;
        $sgstAmount = $hasGst
            ? round($totalTaxRate > 0 ? $tax * ($sgstRate / $totalTaxRate) : $tax / 2, 2)
            : 0.0;

        $items = collect($formattedOrder['items'] ?? [])->map(function (array $item) use ($hasGst) {
            $lineSubtotal = (float) ($item['lineSubtotal'] ?? 0);
            $itemDiscount = (float) ($item['discount'] ?? 0);
            $itemTax = $hasGst ? (float) ($item['tax'] ?? 0) : 0.0;
            $lineTotal = (float) ($item['total'] ?? ($lineSubtotal + $itemTax - $itemDiscount));

            return [
                'name' => $item['displayName'] ?? $item['name'] ?? 'Item',
                'quantity' => (int) ($item['qty'] ?? 0),
                'unit_price' => (float) ($item['unitPrice'] ?? 0),
                'line_subtotal' => $lineSubtotal,
                'discount' => $itemDiscount,
                'tax' => $itemTax,
                'line_total' => $hasGst ? $lineTotal : round(max(0, $lineSubtotal - $itemDiscount), 2),
                'special_instructions' => $item['specialInstructions'] ?? null,
            ];
        })->values()->all();

        return [
            'bill_no' => $formattedOrder['displayId'] ?? $order->compactNumber(),
            'order_number' => $order->order_number,
            'date' => trim(($formattedOrder['date'] ?? '').' '.($formattedOrder['time'] ?? '')),
            'channel' => $formattedOrder['channel'] ?? 'Dining',
            'location' => $formattedOrder['location'] ?? 'Direct Order',
            'customer' => $formattedOrder['customer'] ?? ($order->customer_name ?: 'Walk-in Customer'),
            'phone' => $formattedOrder['phone'] ?? ($order->customer_phone ?: 'N/A'),
            'email' => $order->customer_email,
            'payment_label' => $formattedOrder['paymentLabel'] ?? ucfirst($order->payment_status),
            'status_label' => $formattedOrder['statusLabel'] ?? ucfirst($order->order_status),
            'note' => $formattedOrder['note'] ?? $order->notes,
            'coupon_code' => $formattedOrder['couponCode'] ?? $order->coupon?->code,
            'has_gst' => $hasGst,
            'cgst_rate' => $cgstRate,
            'sgst_rate' => $sgstRate,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'taxable_after_discount' => $taxableAfterDiscount,
            'tax' => $tax,
            'cgst_amount' => $cgstAmount,
            'sgst_amount' => $sgstAmount,
            'total' => $total,
            'items' => $items,
        ];
    }
}
