<?php

namespace Tests\Feature;

use App\Mail\CustomerOrderReceiptMail;
use App\Models\Business;
use App\Models\Coupon;
use App\Models\MailSetting;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\ServicePoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerScannerApiTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private ServicePoint $servicePoint;
    private MenuCategory $category;
    private MenuItem $menuItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Customer QR Cafe',
            'type' => 'restaurant',
            'status' => 'active',
        ]);

        $this->servicePoint = ServicePoint::create([
            'business_id' => $this->business->id,
            'code' => 'QR-001',
            'qr_identifier' => 'customer-qr-001',
            'name' => 'Garden Table',
            'seats' => 4,
            'category' => 'Garden',
            'point_type' => 'table',
            'status' => 'available',
            'is_active' => true,
        ]);

        $this->category = MenuCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Starters',
            'code' => 'STA',
            'active' => true,
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $this->menuItem = MenuItem::create([
            'business_id' => $this->business->id,
            'menu_category_id' => $this->category->id,
            'name' => 'Customer Paneer Tikka',
            'description' => 'Smoky paneer starter',
            'category' => 'Starters',
            'type' => 'veg',
            'price' => 180,
            'tax_rate' => 5,
            'stock' => true,
            'availability' => true,
            'status' => 'active',
            'sort_order' => 1,
        ]);
    }

    public function test_customer_can_open_scanner_menu_without_token(): void
    {
        $otherBusiness = Business::create([
            'name' => 'Other QR Cafe',
            'type' => 'restaurant',
            'status' => 'active',
        ]);

        MenuItem::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Other Business Burger',
            'category' => 'Starters',
            'type' => 'veg',
            'price' => 99,
            'stock' => true,
            'availability' => true,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/customer/scanner/customer-qr-001/menu');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('business.name', 'Customer QR Cafe')
            ->assertJsonFragment(['name' => 'Customer Paneer Tikka'])
            ->assertJsonMissing(['name' => 'Other Business Burger']);
    }

    public function test_customer_can_open_scanner_menu_with_service_point_code_without_token(): void
    {
        $response = $this->getJson('/api/v1/customer/scanner/QR-001/menu');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('business.name', 'Customer QR Cafe')
            ->assertJsonFragment(['name' => 'Customer Paneer Tikka']);
    }

    public function test_customer_can_get_current_coupons_without_token(): void
    {
        Coupon::create([
            'business_id' => $this->business->id,
            'code' => 'WELCOME10',
            'type' => 'percentage',
            'value' => 10,
            'minimum_order' => 100,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'is_active' => true,
        ]);

        Coupon::create([
            'business_id' => $this->business->id,
            'code' => 'EXPIRED',
            'type' => 'flat',
            'value' => 20,
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/customer/scanner/customer-qr-001/coupons');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.context.id', $this->servicePoint->id)
            ->assertJsonFragment(['code' => 'WELCOME10'])
            ->assertJsonMissing(['code' => 'EXPIRED']);
    }

    public function test_customer_can_create_order_from_scanner_without_token(): void
    {
        $response = $this->postJson('/api/v1/customer/scanner/customer-qr-001/orders', [
            'customer_name' => 'Walk In Guest',
            'customer_phone' => '9999999999',
            'customer_email' => 'guest@example.com',
            'items' => [
                [
                    'menu_item_id' => $this->menuItem->id,
                    'quantity' => 2,
                    'special_instructions' => 'Less spicy',
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.context.id', $this->servicePoint->id)
            ->assertJsonPath('data.order.customer_name', 'Walk In Guest')
            ->assertJsonPath('data.order.customer_email', 'guest@example.com')
            ->assertJsonPath('data.order.service_point_id', $this->servicePoint->id);

        $this->assertDatabaseHas('orders', [
            'business_id' => $this->business->id,
            'service_point_id' => $this->servicePoint->id,
            'customer_name' => 'Walk In Guest',
            'customer_phone' => '9999999999',
            'customer_email' => 'guest@example.com',
            'order_status' => 'preparing',
        ]);

        $this->assertDatabaseHas('order_items', [
            'menu_item_id' => $this->menuItem->id,
            'item_name' => 'Customer Paneer Tikka',
            'quantity' => 2,
            'special_instructions' => 'Less spicy',
        ]);
    }

    public function test_customer_scanner_order_accepts_item_id_alias_for_menu_item_id(): void
    {
        $response = $this->postJson('/api/v1/customer/scanner/customer-qr-001/orders', [
            'customer_name' => 'Alias Guest',
            'items' => [
                [
                    'id' => $this->menuItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order.service_point_id', $this->servicePoint->id);

        $this->assertDatabaseHas('order_items', [
            'menu_item_id' => $this->menuItem->id,
            'item_name' => 'Customer Paneer Tikka',
            'quantity' => 1,
        ]);
    }

    public function test_customer_scanner_order_accepts_camel_case_item_payload(): void
    {
        $response = $this->postJson('/api/v1/customer/scanner/customer-qr-001/orders', [
            'customer_name' => 'Camel Guest',
            'items' => [
                [
                    'menuItemId' => $this->menuItem->id,
                    'qty' => 2,
                    'specialInstructions' => 'No chilli',
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order.service_point_id', $this->servicePoint->id);

        $this->assertDatabaseHas('order_items', [
            'menu_item_id' => $this->menuItem->id,
            'item_name' => 'Customer Paneer Tikka',
            'quantity' => 2,
            'special_instructions' => 'No chilli',
        ]);
    }

    public function test_customer_scanner_order_returns_first_validation_message_when_payload_is_invalid(): void
    {
        $response = $this->postJson('/api/v1/customer/scanner/customer-qr-001/orders', [
            'items' => [
                [
                    'quantity' => 0,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'The items.0.menu_item_id field is required.')
            ->assertJsonStructure([
                'errors' => [
                    'items.0.menu_item_id',
                    'items.0.quantity',
                ],
            ]);
    }

    public function test_customer_order_email_is_sent_with_track_and_bill_links_when_email_is_present(): void
    {
        Mail::fake();

        $this->business->update([
            'name' => 'Customer QR Cafe',
            'address' => 'MG Road',
            'pincode' => '560001',
            'gst_number' => 'GST1234567',
            'gst_enabled' => true,
            'cgst' => 2.5,
            'sgst' => 2.5,
        ]);

        MailSetting::create([
            'enabled' => true,
            'mailer' => 'smtp',
            'host' => 'smtp.example.test',
            'port' => 587,
            'from_address' => 'orders@example.test',
            'from_name' => 'Customer QR Cafe',
            'timeout' => 30,
        ]);

        $response = $this->postJson('/api/v1/customer/scanner/customer-qr-001/orders', [
            'customer_name' => 'Guest With Mail',
            'customer_phone' => '9999999999',
            'customer_email' => 'guest@example.com',
            'items' => [
                [
                    'menu_item_id' => $this->menuItem->id,
                    'quantity' => 2,
                    'special_instructions' => 'Less spicy',
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order.customer_email', 'guest@example.com');

        $links = $response->json('data.links');

        $this->assertIsArray($links);
        $this->assertStringContainsString('/orders/track/', $links['track_url'] ?? '');
        $this->assertStringContainsString('/orders/track/', $links['status_url'] ?? '');
        $this->assertStringContainsString('/orders/track/', $links['bill_url'] ?? '');

        Mail::assertSent(CustomerOrderReceiptMail::class, function (CustomerOrderReceiptMail $mail) {
            return $mail->hasTo('guest@example.com')
                && ($mail->payload['receipt']['email'] ?? null) === 'guest@example.com'
                && filled($mail->payload['links']['track_url'] ?? null)
                && filled($mail->payload['links']['bill_print_url'] ?? null)
                && ($mail->payload['receipt']['has_gst'] ?? false) === true;
        });
    }

    public function test_customer_order_email_flow_is_skipped_when_email_is_missing(): void
    {
        Mail::fake();

        MailSetting::create([
            'enabled' => true,
            'mailer' => 'smtp',
            'host' => 'smtp.example.test',
            'port' => 587,
            'from_address' => 'orders@example.test',
            'from_name' => 'Customer QR Cafe',
            'timeout' => 30,
        ]);

        $response = $this->postJson('/api/v1/customer/scanner/customer-qr-001/orders', [
            'customer_name' => 'Guest Without Mail',
            'customer_phone' => '9999999999',
            'items' => [
                [
                    'menu_item_id' => $this->menuItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order.customer_email', null);

        Mail::assertNothingSent();
    }

    public function test_customer_repeat_scanner_order_adds_items_to_existing_live_order_instead_of_creating_new_order(): void
    {
        Mail::fake();

        MailSetting::create([
            'enabled' => true,
            'mailer' => 'smtp',
            'host' => 'smtp.example.test',
            'port' => 587,
            'from_address' => 'orders@example.test',
            'from_name' => 'Customer QR Cafe',
            'timeout' => 30,
        ]);

        $firstResponse = $this->postJson('/api/v1/customer/scanner/customer-qr-001/orders', [
            'customer_name' => 'Repeat Guest',
            'customer_phone' => '9999999999',
            'customer_email' => 'repeat@example.com',
            'items' => [
                [
                    'menu_item_id' => $this->menuItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $firstResponse->assertCreated()
            ->assertJsonPath('data.mode', 'created')
            ->assertJsonPath('data.merged_into_existing_order', false);

        $firstOrderNumber = $firstResponse->json('data.order.order_number');

        $secondResponse = $this->postJson('/api/v1/customer/scanner/customer-qr-001/orders', [
            'notes' => 'Add one more plate',
            'items' => [
                [
                    'menu_item_id' => $this->menuItem->id,
                    'quantity' => 2,
                    'special_instructions' => 'Extra onion',
                ],
            ],
        ]);

        $secondResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.mode', 'updated')
            ->assertJsonPath('data.merged_into_existing_order', true)
            ->assertJsonPath('data.order.order_number', $firstOrderNumber)
            ->assertJsonPath('data.order.customer_email', 'repeat@example.com');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 2);

        $order = Order::where('order_number', $firstOrderNumber)->firstOrFail();

        $this->assertSame('repeat@example.com', $order->customer_email);
        $this->assertStringContainsString('Add one more plate', (string) $order->notes);
        $this->assertSame(3, (int) $order->items()->sum('quantity'));

        Mail::assertSent(CustomerOrderReceiptMail::class, 2);
        Mail::assertSent(CustomerOrderReceiptMail::class, function (CustomerOrderReceiptMail $mail) {
            return ! $mail->isUpdate
                && $mail->hasTo('repeat@example.com')
                && str_contains($mail->envelope()->subject, 'details and bill');
        });
        Mail::assertSent(CustomerOrderReceiptMail::class, function (CustomerOrderReceiptMail $mail) {
            return $mail->isUpdate
                && $mail->hasTo('repeat@example.com')
                && str_contains($mail->envelope()->subject, 'bill is updated');
        });
    }

    public function test_customer_can_open_signed_tracking_and_bill_pages_for_scanner_order(): void
    {
        $this->business->update([
            'name' => 'Customer QR Cafe',
            'address' => 'MG Road',
            'pincode' => '560001',
            'gst_number' => 'GST1234567',
            'gst_enabled' => true,
            'cgst' => 2.5,
            'sgst' => 2.5,
        ]);

        $response = $this->postJson('/api/v1/customer/scanner/customer-qr-001/orders', [
            'customer_name' => 'Tracking Guest',
            'customer_phone' => '9999999999',
            'customer_email' => 'tracking@example.com',
            'notes' => 'No onions',
            'items' => [
                [
                    'menu_item_id' => $this->menuItem->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertCreated();

        $links = $response->json('data.links');

        $this->get($this->relativePathFromUrl($links['track_url']))
            ->assertOk()
            ->assertSee('Track Order')
            ->assertSee('Customer QR Cafe')
            ->assertSee('Tracking Guest');

        $this->getJson($this->relativePathFromUrl($links['status_url']))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.receipt.email', 'tracking@example.com')
            ->assertJsonPath('data.receipt.note', 'No onions')
            ->assertJsonPath('data.business.gst_enabled', true);

        $this->get($this->relativePathFromUrl($links['bill_url']))
            ->assertOk()
            ->assertSee('GST Bill Summary')
            ->assertSee('GSTIN: GST1234567')
            ->assertSee('Tracking Guest');
    }

    public function test_customer_bill_page_hides_gst_summary_when_gst_is_disabled(): void
    {
        $this->business->update([
            'name' => 'Customer QR Cafe',
            'gst_enabled' => false,
            'cgst' => 0,
            'sgst' => 0,
        ]);

        $response = $this->postJson('/api/v1/customer/scanner/customer-qr-001/orders', [
            'customer_name' => 'No GST Guest',
            'items' => [
                [
                    'menu_item_id' => $this->menuItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertCreated();

        $links = $response->json('data.links');

        $this->get($this->relativePathFromUrl($links['bill_url']))
            ->assertOk()
            ->assertSee('Bill Summary')
            ->assertDontSee('GST Bill Summary')
            ->assertDontSee('Total after GST');
    }

    private function relativePathFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $query = parse_url($url, PHP_URL_QUERY);

        return $query ? $path.'?'.$query : $path;
    }
}
