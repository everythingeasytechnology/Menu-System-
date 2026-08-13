<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signInBusinessOwner('staff-page-owner@example.com');
    }

    public function test_staff_profile_image_can_be_created_updated_and_removed(): void
    {
        Storage::fake('public');

        $createResponse = $this->post('/staff', [
            'name' => 'Rahul Waiter',
            'email' => 'rahul.profile@example.com',
            'phone' => '+919999999999',
            'role' => 'waiter',
            'status' => 'active',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'profile_image' => UploadedFile::fake()->image('rahul.jpg', 300, 300),
        ]);

        $staff = User::where('email', 'rahul.profile@example.com')->firstOrFail();

        $createResponse->assertCreated()
            ->assertJsonPath('staff.name', 'Rahul Waiter')
            ->assertJsonPath('staff.profileImageUrl', asset('storage/'.$staff->profile_image_path));

        Storage::disk('public')->assertExists($staff->profile_image_path);

        $oldPath = $staff->profile_image_path;

        $updateResponse = $this->post('/staff/'.$staff->id, [
            '_method' => 'PUT',
            'name' => 'Rahul Manager',
            'email' => 'rahul.profile@example.com',
            'phone' => '+918888888888',
            'role' => 'manager',
            'status' => 'active',
            'password' => '',
            'password_confirmation' => '',
            'profile_image' => UploadedFile::fake()->image('rahul-new.png', 300, 300),
        ]);

        $staff->refresh();

        $updateResponse->assertOk()
            ->assertJsonPath('staff.name', 'Rahul Manager')
            ->assertJsonPath('staff.profileImageUrl', asset('storage/'.$staff->profile_image_path));

        $this->assertNotSame($oldPath, $staff->profile_image_path);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($staff->profile_image_path);

        $newPath = $staff->profile_image_path;

        $removeResponse = $this->post('/staff/'.$staff->id, [
            '_method' => 'PUT',
            'name' => 'Rahul Manager',
            'email' => 'rahul.profile@example.com',
            'phone' => '+918888888888',
            'role' => 'manager',
            'status' => 'active',
            'password' => '',
            'password_confirmation' => '',
            'remove_profile_image' => '1',
        ]);

        $staff->refresh();

        $removeResponse->assertOk()
            ->assertJsonPath('staff.profileImageUrl', null);

        $this->assertNull($staff->profile_image_path);
        Storage::disk('public')->assertMissing($newPath);
    }
}
