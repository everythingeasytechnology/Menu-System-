<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BusinessRegistrationSuccessMail;
use App\Models\Business;
use App\Models\BusinessSetting;
use App\Models\MailSetting;
use App\Models\Order;
use App\Models\PresetFoodImage;
use App\Models\User;
use App\Services\BusinessOwnerPdfService;
use App\Services\MailSettingsService;
use App\Services\MenuImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class SuperAdminController extends Controller
{
    public function __construct(
        private readonly MenuImageService $menuImageService,
        private readonly MailSettingsService $mailSettingsService,
        private readonly BusinessOwnerPdfService $businessOwnerPdfService,
    ) {}

    public const BUSINESS_STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ];

    public const OWNER_STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ];

    public function index(Request $request)
    {
        return view('admin.dashboard', [
            'stats' => $this->stats(),
            'charts' => $this->charts(),
        ]);
    }

    public function businesses(Request $request)
    {
        $businessQuery = Business::query()
            ->whereHas('owner', fn ($owner) => $owner->where('role', 'owner'))
            ->with(['owner', 'businessSetting'])
            ->withCount(['orders'])
            ->latest();

        if ($request->filled('business_search')) {
            $search = trim((string) $request->input('business_search'));
            $businessQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('business_status') && $request->input('business_status') !== 'all') {
            $businessQuery->where('status', $request->input('business_status'));
        }

        return view('admin.businesses.index', [
            'stats' => $this->stats(),
            'businesses' => $businessQuery->paginate(30)->withQueryString(),
            'businessStatuses' => self::BUSINESS_STATUSES,
            'ownerStatuses' => self::OWNER_STATUSES,
            'filters' => $request->only(['business_search', 'business_status']),
        ]);
    }

    public function createBusiness()
    {
        return view('admin.businesses.create', [
            'businessStatuses' => self::BUSINESS_STATUSES,
        ]);
    }

    public function editBusiness(Business $business)
    {
        $this->abortUnlessOwnerBusiness($business);

        return view('admin.businesses.edit', [
            'business' => $business
                ->load(['owner', 'businessSetting'])
                ->loadCount(['orders', 'menuItems', 'categories', 'restaurantTables', 'rooms', 'servicePoints']),
            'businessStatuses' => self::BUSINESS_STATUSES,
            'ownerStatuses' => self::OWNER_STATUSES,
        ]);
    }

    public function storeBusiness(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'max:50'],
            'business_status' => ['required', 'string', Rule::in(array_keys(self::BUSINESS_STATUSES))],
            'business_email' => ['nullable', 'email', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'owner_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        [$owner, $business] = DB::transaction(function () use ($data) {
            $owner = User::create([
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'phone' => $data['owner_phone'] ?? null,
                'password' => Hash::make($data['owner_password']),
                'role' => 'owner',
                'status' => 'active',
            ]);

            $business = Business::create([
                'owner_user_id' => $owner->id,
                'name' => $data['business_name'],
                'type' => $data['business_type'],
                'email' => $data['business_email'] ?? null,
                'phone' => $data['business_phone'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? 'India',
                'timezone' => 'Asia/Kolkata',
                'status' => $data['business_status'],
            ]);

            $owner->update(['business_id' => $business->id]);

            BusinessSetting::updateOrCreate(
                ['business_id' => $business->id],
                [
                    'brand_name' => $business->name,
                    'business_email' => $business->email,
                    'country' => $business->country,
                    'state' => $business->state,
                    'gst_enabled' => false,
                    'cgst' => 2.5,
                    'sgst' => 2.5,
                ],
            );

            return [$owner->fresh(), $business->fresh()];
        });

        $this->sendMailIfEnabled(fn () => Mail::to($owner->email)->send(new BusinessRegistrationSuccessMail($owner, $business)));

        return redirect()
            ->route('admin.businesses.index')
            ->with('success', 'Business and owner account created.');
    }

    public function updateBusiness(Request $request, Business $business): RedirectResponse
    {
        $this->abortUnlessOwnerBusiness($business);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', Rule::in(array_keys(self::BUSINESS_STATUSES))],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($business->owner_user_id)],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'owner_status' => ['nullable', 'string', Rule::in(array_keys(self::OWNER_STATUSES))],
        ]);

        $businessData = collect($data)
            ->only(['name', 'type', 'status', 'email', 'phone', 'city', 'state', 'country'])
            ->all();

        $business->update($businessData);

        $owner = $business->owner()->first();
        if ($owner) {
            $owner->update([
                'name' => ($data['owner_name'] ?? null) ?: $owner->name,
                'email' => ($data['owner_email'] ?? null) ?: $owner->email,
                'phone' => $data['owner_phone'] ?? null,
                'status' => $data['owner_status'] ?? $owner->status,
            ]);

            if ($owner->status !== 'active') {
                $owner->accessTokens()->update(['revoked_at' => now()]);
            }
        }

        BusinessSetting::where('business_id', $business->id)->update([
            'brand_name' => $business->name,
            'business_email' => $business->email,
            'country' => $business->country,
            'state' => $business->state,
        ]);

        return redirect()
            ->route('admin.businesses.edit', $business)
            ->with('success', 'Business updated.');
    }

    public function downloadBusinessPdf(Business $business)
    {
        $this->abortUnlessOwnerBusiness($business);

        $business->load(['owner', 'businessSetting']);
        $pdf = $this->businessOwnerPdfService->make($business);
        $filename = Str::slug($business->name ?: 'business-owner').'-details.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => strlen($pdf),
        ]);
    }

    public function menuImages(Request $request)
    {
        $query = PresetFoodImage::query()->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($preset) use ($search) {
                $preset->where('name', 'like', '%'.$search.'%')
                    ->orWhere('tags', 'like', '%'.$search.'%');
            });
        }

        return view('admin.menu-images.index', [
            'images' => $query->paginate(24)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function storeMenuImage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:500'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
        ]);

        $path = $this->menuImageService->compressAndStoreImage($request->file('image'));

        PresetFoodImage::create([
            'name' => $data['name'],
            'tags' => $data['tags'] ?? strtolower($data['name']),
            'image_path' => 'storage/'.$path,
        ]);

        return redirect()
            ->route('admin.menu-images.index')
            ->with('success', 'Menu image uploaded.');
    }

    public function updateMenuImage(Request $request, PresetFoodImage $presetFoodImage): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $this->deleteUploadedPresetFile($presetFoodImage->image_path);
            $data['image_path'] = 'storage/'.$this->menuImageService->compressAndStoreImage($request->file('image'));
        }

        $presetFoodImage->update([
            'name' => $data['name'],
            'tags' => $data['tags'] ?? strtolower($data['name']),
            'image_path' => $data['image_path'] ?? $presetFoodImage->image_path,
        ]);

        return redirect()
            ->route('admin.menu-images.index')
            ->with('success', 'Menu image updated.');
    }

    public function destroyMenuImage(PresetFoodImage $presetFoodImage): RedirectResponse
    {
        $this->deleteUploadedPresetFile($presetFoodImage->image_path);
        $presetFoodImage->delete();

        return redirect()
            ->route('admin.menu-images.index')
            ->with('success', 'Menu image deleted.');
    }

    public function mailSettings()
    {
        return view('admin.mail-settings.edit', [
            'setting' => MailSetting::query()->first(),
            'encryptions' => [
                'none' => 'None',
                'tls' => 'TLS',
                'ssl' => 'SSL',
            ],
        ]);
    }

    public function updateMailSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'host' => [
                'required_if:enabled,1',
                'nullable',
                'string',
                'max:255',
                'not_regex:/@/',
                'not_regex:/^https?:\/\//i',
                'not_regex:/:\d+$/',
            ],
            'port' => ['required_if:enabled,1', 'nullable', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['nullable', 'string', Rule::in(['none', 'tls', 'ssl'])],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:1000'],
            'from_address' => ['required_if:enabled,1', 'nullable', 'email', 'max:255'],
            'from_name' => ['required_if:enabled,1', 'nullable', 'string', 'max:255'],
            'timeout' => ['nullable', 'integer', 'min:1', 'max:120'],
        ], [
            'host.not_regex' => 'SMTP Host must be a server name like smtp.gmail.com or mail.everythingeasy.in, not an email, URL, or host with port.',
        ]);

        $setting = MailSetting::query()->firstOrNew([]);
        $payload = [
            'enabled' => (bool) $data['enabled'],
            'mailer' => 'smtp',
            'host' => $data['host'] ?? null,
            'port' => $data['port'] ?? null,
            'encryption' => ($data['encryption'] ?? 'none') === 'none' ? null : $data['encryption'],
            'username' => $data['username'] ?? null,
            'from_address' => $data['from_address'] ?? null,
            'from_name' => $data['from_name'] ?? null,
            'timeout' => $data['timeout'] ?? 30,
        ];

        if (filled($data['password'] ?? null)) {
            $payload['password'] = $data['password'];
        }

        $setting->fill($payload)->save();
        $this->mailSettingsService->apply($setting);

        return redirect()
            ->route('admin.mail-settings.edit')
            ->with('success', 'SMTP settings updated.');
    }

    public function testMailSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        $setting = MailSetting::query()->first();

        if (! $setting || ! $setting->enabled) {
            return redirect()
                ->route('admin.mail-settings.edit')
                ->with('error', 'Enable and save SMTP settings before sending a test email.');
        }

        try {
            $this->mailSettingsService->apply($setting);

            Mail::raw('This is a test email from EverythingEasy ServiceOS SMTP settings.', function ($message) use ($data) {
                $message->to($data['test_email'])->subject('SMTP test email');
            });

            $setting->forceFill([
                'last_tested_at' => now(),
                'last_test_status' => 'success',
                'last_test_message' => 'Test email sent to '.$data['test_email'],
            ])->save();

            return redirect()
                ->route('admin.mail-settings.edit')
                ->with('success', 'Test email sent successfully.');
        } catch (Throwable $exception) {
            $setting->forceFill([
                'last_tested_at' => now(),
                'last_test_status' => 'failed',
                'last_test_message' => $exception->getMessage(),
            ])->save();

            return redirect()
                ->route('admin.mail-settings.edit')
                ->with('error', 'Test email failed: '.$exception->getMessage());
        }
    }

    private function stats(): array
    {
        return [
            'businesses' => Business::count(),
            'active_businesses' => Business::where('status', 'active')->count(),
            'inactive_businesses' => Business::where('status', 'inactive')->count(),
            'suspended_businesses' => Business::where('status', 'suspended')->count(),
            'owners' => User::where('role', 'owner')->count(),
            'active_owners' => User::where('role', 'owner')->where('status', 'active')->count(),
            'inactive_owners' => User::where('role', 'owner')->where('status', 'inactive')->count(),
            'suspended_owners' => User::where('role', 'owner')->where('status', 'suspended')->count(),
            'orders' => Order::count(),
            'live_orders' => Order::whereIn('order_status', Order::ACTIVE_STATUSES)->count(),
            'gross_sales' => (float) Order::where('order_status', '!=', 'cancelled')->sum('total'),
        ];
    }

    private function abortUnlessOwnerBusiness(Business $business): void
    {
        $business->loadMissing('owner');

        abort_if(! $business->owner || $business->owner->role !== 'owner', 404);
    }

    private function charts(): array
    {
        $from = now()->startOfMonth()->subMonths(5);
        $orders = Order::query()
            ->where('created_at', '>=', $from)
            ->where('order_status', '!=', 'cancelled')
            ->get(['created_at', 'total']);

        $monthly = collect(range(5, 0))
            ->map(function (int $offset) use ($orders) {
                $month = now()->startOfMonth()->subMonths($offset);
                $monthOrders = $orders->filter(fn (Order $order) => $order->created_at?->format('Y-m') === $month->format('Y-m'));

                return [
                    'label' => $month->format('M'),
                    'orders' => $monthOrders->count(),
                    'sales' => (float) $monthOrders->sum('total'),
                ];
            });

        $maxOrders = max(1, (int) $monthly->max('orders'));
        $maxSales = max(1, (float) $monthly->max('sales'));

        return [
            'monthly' => $monthly
                ->map(function (array $month) use ($maxOrders, $maxSales) {
                    return [
                        ...$month,
                        'order_height' => $month['orders'] > 0 ? max(8, round(($month['orders'] / $maxOrders) * 100)) : 4,
                        'sales_height' => $month['sales'] > 0 ? max(8, round(($month['sales'] / $maxSales) * 100)) : 4,
                    ];
                })
                ->values(),
            'business_statuses' => $this->statusDistribution([
                'Active' => Business::where('status', 'active')->count(),
                'Inactive' => Business::where('status', 'inactive')->count(),
                'Suspended' => Business::where('status', 'suspended')->count(),
            ]),
            'owner_statuses' => $this->statusDistribution([
                'Active' => User::where('role', 'owner')->where('status', 'active')->count(),
                'Inactive' => User::where('role', 'owner')->where('status', 'inactive')->count(),
                'Suspended' => User::where('role', 'owner')->where('status', 'suspended')->count(),
            ]),
        ];
    }

    private function statusDistribution(array $counts): array
    {
        $total = max(1, array_sum($counts));

        return collect($counts)
            ->map(fn (int $count, string $label) => [
                'label' => $label,
                'count' => $count,
                'percent' => round(($count / $total) * 100),
            ])
            ->values()
            ->all();
    }

    private function deleteUploadedPresetFile(?string $imagePath): void
    {
        if (! $imagePath || ! str_starts_with($imagePath, 'storage/')) {
            return;
        }

        Storage::disk('public')->delete(substr($imagePath, strlen('storage/')));
    }

    private function sendMailIfEnabled(callable $callback): bool
    {
        if (! $this->mailSettingsService->apply()) {
            return false;
        }

        try {
            $callback();

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
