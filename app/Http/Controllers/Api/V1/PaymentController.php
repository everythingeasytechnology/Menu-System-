<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Payments\StorePaymentRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends ApiController
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $payments = Payment::where('business_id', $this->businessId($request))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate((int) $request->input('per_page', 25));

        return $this->success(PaymentResource::collection($payments), 'Payments');
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $order = Order::where('business_id', $this->businessId($request))->whereKey($data['order_id'])->first();

        if (! $order) {
            return $this->error('Order not found', 404);
        }

        $payment = DB::transaction(function () use ($data, $order, $request) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'business_id' => $order->business_id,
                'payment_method' => $data['payment_method'],
                'payment_gateway' => $data['payment_gateway'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'amount' => $data['amount'],
                'status' => $data['status'],
                'paid_at' => $data['paid_at'] ?? ($data['status'] === 'paid' ? now() : null),
            ]);

            if ($payment->status === 'paid') {
                $order->update(['payment_status' => 'paid']);
            }

            $this->auditLogService->record($request->user(), $order->business_id, 'payment.created', $payment);

            return $payment;
        });

        return $this->success(new PaymentResource($payment), 'Payment recorded', 201);
    }

    public function show(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->business_id !== $this->businessId($request)) {
            return $this->error('Resource not found', 404);
        }

        return $this->success(new PaymentResource($payment), 'Payment details');
    }
}
