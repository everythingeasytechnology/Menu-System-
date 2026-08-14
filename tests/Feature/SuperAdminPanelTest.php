<?php

namespace Tests\Feature;

use App\Models\AppVersion;
use App\Models\Business;
use App\Models\MailSetting;
use App\Models\PresetFoodImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuperAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_login_and_open_admin_dashboard(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->post('/login', [
            'email' => 'superadmin@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get('/admin')
            ->assertOk()
            ->assertSee('ServiceOS Admin')
            ->assertSee('Business Overview')
            ->assertSee('Orders and Sales')
            ->assertSee('View Businesses')
            ->assertSee('Create Business')
            ->assertSee('Menu Images')
            ->assertSee('Mail Settings')
            ->assertSee('App Version');
    }

    public function test_owner_cannot_open_superadmin_section(): void
    {
        $this->signInBusinessOwner('owner-admin-block@example.com');

        $this->get('/admin')
            ->assertRedirect(route('dashboard'));
    }

    public function test_admin_dashboard_shows_business_owners_not_staff_users(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'owners-only-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Visible Owner',
            'email' => 'visible-owner@example.com',
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $business = Business::create([
            'owner_user_id' => $owner->id,
            'name' => 'Owner Only Cafe',
            'type' => 'restaurant',
            'country' => 'India',
            'timezone' => 'Asia/Kolkata',
            'status' => 'active',
        ]);

        $owner->update(['business_id' => $business->id]);

        User::create([
            'business_id' => $business->id,
            'name' => 'Hidden Waiter',
            'email' => 'hidden-waiter@example.com',
            'password' => 'password123',
            'role' => 'waiter',
            'status' => 'active',
        ]);

        $this->actingAs($superadmin);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Business Overview')
            ->assertDontSee('Owner Only Cafe')
            ->assertDontSee('Visible Owner')
            ->assertDontSee('Hidden Waiter');

        $this->get(route('admin.businesses.index'))
            ->assertOk()
            ->assertSee('Owner Only Cafe')
            ->assertSee('Visible Owner')
            ->assertSee('Edit')
            ->assertDontSee('Hidden Waiter')
            ->assertDontSee('hidden-waiter@example.com')
            ->assertDontSee('Control platform users');

        $this->get(route('admin.businesses.edit', $business))
            ->assertOk()
            ->assertSee('Edit Business Details')
            ->assertSee('Owner Only Cafe')
            ->assertSee('Visible Owner');
    }

    public function test_business_owner_details_include_settings_and_pdf_download(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'owner-pdf-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'PDF Owner',
            'email' => 'pdf-owner@example.com',
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $ownerBusiness = Business::create([
            'owner_user_id' => $owner->id,
            'name' => 'Owner PDF Cafe',
            'type' => 'restaurant',
            'email' => 'owner-pdf-cafe@example.com',
            'phone' => '9999999999',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'country' => 'India',
            'timezone' => 'Asia/Kolkata',
            'status' => 'active',
        ]);

        $owner->update(['business_id' => $ownerBusiness->id]);

        $ownerBusiness->update([
            'business_email' => 'settings@example.com',
            'shop_no' => 'A-12',
            'address' => 'Main Market',
            'country' => 'India',
            'state' => 'Delhi',
            'district' => 'Central Delhi',
            'pincode' => '110001',
            'gst_number' => '07ABCDE1234F1Z5',
            'gst_enabled' => true,
            'cgst' => 2.5,
            'sgst' => 2.5,
        ]);

        $staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff-owned-business@example.com',
            'password' => 'password123',
            'role' => 'waiter',
            'status' => 'active',
        ]);

        $staffBusiness = Business::create([
            'owner_user_id' => $staff->id,
            'name' => 'Staff Owned Business',
            'type' => 'restaurant',
            'status' => 'active',
        ]);

        $this->actingAs($superadmin);

        $this->get(route('admin.businesses.index'))
            ->assertOk()
            ->assertSee('Owner PDF Cafe')
            ->assertSee('PDF')
            ->assertDontSee('Staff Owned Business');

        $this->get(route('admin.businesses.edit', $ownerBusiness))
            ->assertOk()
            ->assertSee('Business Settings')
            ->assertSee('Owner PDF Cafe')
            ->assertSee('07ABCDE1234F1Z5')
            ->assertSee('Download PDF');

        $this->get(route('admin.businesses.edit', $staffBusiness))
            ->assertNotFound();

        $pdf = $this->get(route('admin.businesses.pdf', $ownerBusiness));

        $pdf->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="owner-pdf-cafe-details.pdf"');

        $this->assertStringStartsWith('%PDF-1.4', $pdf->getContent());
        $this->assertStringContainsString('Owner PDF Cafe', $pdf->getContent());
        $this->assertStringContainsString('07ABCDE1234F1Z5', $pdf->getContent());
    }

    public function test_superadmin_can_create_business_with_owner_login(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'create-business-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->actingAs($superadmin);

        $this->post(route('admin.businesses.store'), [
            'business_name' => 'Admin Created Cafe',
            'business_type' => 'restaurant',
            'business_status' => 'active',
            'business_email' => 'cafe@example.com',
            'business_phone' => '9999999999',
            'city' => 'Dehradun',
            'state' => 'Uttarakhand',
            'country' => 'India',
            'owner_name' => 'Cafe Owner',
            'owner_email' => 'cafe-owner@example.com',
            'owner_phone' => '8888888888',
            'owner_password' => 'password123',
            'owner_password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.businesses.index'));

        $this->assertDatabaseHas('businesses', [
            'name' => 'Admin Created Cafe',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'cafe-owner@example.com',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->get(route('admin.businesses.create'))
            ->assertOk()
            ->assertSee('Business Details')
            ->assertSee('Owner Login');
    }

    public function test_superadmin_can_suspend_business_and_owner_access_is_blocked(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'suspend-business-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $owner = User::create([
            'name' => 'Owner',
            'email' => 'suspended-owner@example.com',
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $business = Business::create([
            'owner_user_id' => $owner->id,
            'name' => 'Suspend Me',
            'type' => 'restaurant',
            'country' => 'India',
            'timezone' => 'Asia/Kolkata',
            'status' => 'active',
        ]);

        $owner->update(['business_id' => $business->id]);

        $this->actingAs($superadmin)
            ->put(route('admin.businesses.update', $business), [
                'name' => 'Suspend Me',
                'type' => 'restaurant',
                'status' => 'suspended',
                'email' => 'suspend@example.com',
                'phone' => null,
                'city' => null,
                'state' => null,
                'country' => 'India',
            ])
            ->assertRedirect(route('admin.businesses.edit', $business));

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'status' => 'suspended',
        ]);

        $this->actingAs($owner)
            ->get('/')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_superadmin_can_manage_menu_image_library(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'menu-images-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->actingAs($superadmin);

        $this->get(route('admin.menu-images.index'))
            ->assertOk()
            ->assertSee('Menu Item Images')
            ->assertSee('Image Library')
            ->assertSee('Upload');

        $this->post(route('admin.menu-images.store'), [
            'name' => 'Paneer Tikka',
            'tags' => 'paneer, starter, veg',
            'image' => UploadedFile::fake()->image('paneer.jpg', 320, 240),
        ])->assertRedirect(route('admin.menu-images.index'));

        $image = PresetFoodImage::where('name', 'Paneer Tikka')->firstOrFail();

        $this->assertStringStartsWith('storage/menu_items/', $image->image_path);
        $this->assertDatabaseHas('preset_food_images', [
            'name' => 'Paneer Tikka',
            'tags' => 'paneer, starter, veg',
        ]);

        $this->put(route('admin.menu-images.update', $image), [
            'name' => 'Paneer Malai Tikka',
            'tags' => 'paneer, creamy, starter',
        ])->assertRedirect(route('admin.menu-images.index'));

        $this->assertDatabaseHas('preset_food_images', [
            'id' => $image->id,
            'name' => 'Paneer Malai Tikka',
            'tags' => 'paneer, creamy, starter',
        ]);

        $this->delete(route('admin.menu-images.destroy', $image))
            ->assertRedirect(route('admin.menu-images.index'));

        $this->assertDatabaseMissing('preset_food_images', [
            'id' => $image->id,
        ]);
    }

    public function test_superadmin_can_manage_smtp_settings(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'smtp-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->actingAs($superadmin);

        $this->get(route('admin.mail-settings.edit'))
            ->assertOk()
            ->assertSee('Mail Settings')
            ->assertSee('SMTP Details')
            ->assertSee('Send Test')
            ->assertSee('Use only the mail server name')
            ->assertSee('info@everythingeasy.in');

        $this->post(route('admin.mail-settings.update'), [
            'enabled' => '1',
            'host' => 'smtp.mailtrap.io',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'smtp-user',
            'password' => 'smtp-secret',
            'from_address' => 'noreply@example.com',
            'from_name' => 'EverythingEasy',
            'timeout' => 45,
        ])->assertRedirect(route('admin.mail-settings.edit'));

        $setting = MailSetting::query()->firstOrFail();

        $this->assertTrue($setting->enabled);
        $this->assertSame('smtp.mailtrap.io', $setting->host);
        $this->assertSame(587, $setting->port);
        $this->assertSame('smtp-secret', $setting->password);
        $this->assertNotSame('smtp-secret', $setting->getRawOriginal('password'));
        $this->assertSame('smtp.mailtrap.io', config('mail.mailers.smtp.host'));
        $this->assertSame('noreply@example.com', config('mail.from.address'));

        $this->post(route('admin.mail-settings.update'), [
            'enabled' => '1',
            'host' => 'smtp.gmail.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'gmail-user',
            'password' => '',
            'from_address' => 'hello@example.com',
            'from_name' => 'ServiceOS',
            'timeout' => 30,
        ])->assertRedirect(route('admin.mail-settings.edit'));

        $setting->refresh();

        $this->assertSame('smtp.gmail.com', $setting->host);
        $this->assertSame('smtp-secret', $setting->password);
    }

    public function test_superadmin_can_manage_app_version(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'version-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->actingAs($superadmin);

        $this->get(route('admin.app-version.edit'))
            ->assertOk()
            ->assertSee('App Version')
            ->assertSee('Version Details')
            ->assertSee('1.0.0');

        $this->post(route('admin.app-version.update'), [
            'version' => '1.2.3',
        ])->assertRedirect(route('admin.app-version.edit'));

        $this->assertSame('1.2.3', AppVersion::current()->version);
    }

    public function test_superadmin_can_update_own_profile_and_password(): void
    {
        Storage::fake('public');

        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'profile-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->actingAs($superadmin);

        $this->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('Admin Profile')
            ->assertSee('Profile Details')
            ->assertSee('Password');

        $this->put(route('admin.profile.update'), [
            'name' => 'Updated Super Admin',
            'email' => 'updated-super@example.com',
            'phone' => '+919999999999',
            'profile_image' => UploadedFile::fake()->image('admin-logo.png', 300, 300),
        ])->assertRedirect(route('admin.profile.edit'));

        $superadmin->refresh();

        $this->assertSame('Updated Super Admin', $superadmin->name);
        $this->assertSame('updated-super@example.com', $superadmin->email);
        $this->assertSame('+919999999999', $superadmin->phone);
        $this->assertNotNull($superadmin->profile_image_path);
        Storage::disk('public')->assertExists($superadmin->profile_image_path);

        $this->put(route('admin.profile.password'), [
            'current_password' => 'password123',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertRedirect(route('admin.profile.edit'));

        $this->assertTrue(Hash::check('new-password123', $superadmin->fresh()->password));
    }

    public function test_smtp_host_cannot_be_an_email_address(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'bad-smtp-host-super@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->actingAs($superadmin);

        $this->from(route('admin.mail-settings.edit'))
            ->post(route('admin.mail-settings.update'), [
                'enabled' => '1',
                'host' => 'noreply@everythingeasy.in',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'noreply@everythingeasy.in',
                'password' => 'smtp-secret',
                'from_address' => 'noreply@everythingeasy.in',
                'from_name' => 'EverythingEasy',
                'timeout' => 30,
            ])
            ->assertRedirect(route('admin.mail-settings.edit'))
            ->assertSessionHasErrors('host');

        $this->assertDatabaseCount('mail_settings', 0);
    }
}
