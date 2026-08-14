<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\RazorpaySetting;
use App\Models\CashSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Show the settings page with current configurations.
     */
    public function index()
    {
        $business = $this->currentBusiness();
        $businessId = $business->id;

        // The view historically reads $business->brand_name / gst_no (business_settings' field
        // names). Map them here so the Blade template doesn't need to change.
        $business->brand_name = $business->name;
        $business->gst_no = $business->gst_number;

        $razorpay = RazorpaySetting::firstOrCreate(['business_id' => $businessId], [
            'enabled' => false,
            'key_id' => '',
            'key_secret' => '',
        ]);

        $cash = CashSetting::firstOrCreate(['business_id' => $businessId], [
            'enabled' => true,
        ]);

        return view('settings', compact('business', 'razorpay', 'cash'));
    }

    /**
     * Update business details.
     */
    public function updateBusiness(Request $request)
    {
        $validated = $request->validate([
            'brand_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'business_email' => 'required|email|max:255',
            'shop_no' => 'required|string|max:255',
            'address' => 'required|string',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'pincode' => 'required|string|max:10',
            'latitude' => 'nullable|string|max:100',
            'longitude' => 'nullable|string|max:100',
            'gst_no' => 'nullable|string|max:50',
        ]);

        $business = $this->currentBusiness();

        $updateData = [
            'name' => $validated['brand_name'],
            'business_email' => $validated['business_email'],
            'shop_no' => $validated['shop_no'],
            'address' => $validated['address'],
            'country' => $validated['country'],
            'state' => $validated['state'],
            'district' => $validated['district'],
            'pincode' => $validated['pincode'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'gst_number' => $validated['gst_no'] ?? null,
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo if it exists
            if ($business->logo_path) {
                Storage::disk('public')->delete($business->logo_path);
            }
            // Store new logo
            $path = $request->file('logo')->store('logos', 'public');
            $updateData['logo_path'] = $path;
        }

        $business->update($updateData);

        return redirect()->back()
            ->with('success', 'Business details updated successfully!')
            ->with('active_tab', 'business');
    }

    /**
     * Update Razorpay keys and toggle status.
     */
    public function updateRazorpay(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'sometimes|boolean',
            'key_id' => 'required_if:enabled,1|nullable|string|max:255',
            'key_secret' => 'nullable|string|max:255',
        ]);

        $razorpay = RazorpaySetting::firstOrCreate(['business_id' => $this->currentBusiness()->id]);

        if ($request->has('enabled') && ! $request->filled('key_secret') && ! $razorpay->key_secret) {
            return redirect()->back()
                ->withErrors(['key_secret' => 'The key secret field is required when Razorpay is enabled.'])
                ->withInput($request->except('key_secret'))
                ->with('active_tab', 'payments');
        }

        $razorpay->update([
            'enabled' => $request->has('enabled'),
            'key_id' => $request->key_id,
            'key_secret' => $request->filled('key_secret') ? $request->key_secret : $razorpay->key_secret,
        ]);

        return redirect()->back()
            ->with('success', 'Razorpay settings updated successfully!')
            ->with('active_tab', 'payments');
    }

    /**
     * Update Cash payment toggle status.
     */
    public function updateCash(Request $request)
    {
        $cash = CashSetting::firstOrCreate(['business_id' => $this->currentBusiness()->id]);
        $cash->update([
            'enabled' => $request->has('enabled'),
        ]);

        return redirect()->back()
            ->with('success', 'Cash payment status updated successfully!')
            ->with('active_tab', 'payments');
    }

    /**
     * Update account password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user() ?? User::first();

        // Fallback: If no user exists in DB, dynamically create one to keep system functional.
        if (!$user) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@everythingeasy.com',
                'password' => Hash::make($request->new_password),
            ]);
            return redirect()->back()
                ->with('success', 'No user account existed. A new admin user was created with the specified password.')
                ->with('active_tab', 'security');
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Current password does not match.'])
                ->with('active_tab', 'security');
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->back()
            ->with('success', 'Password updated successfully!')
            ->with('active_tab', 'security');
    }

    /**
     * Update GST settings.
     */
    public function updateGst(Request $request)
    {
        $validated = $request->validate([
            'gst_no' => 'nullable|string|max:50',
            'gst_enabled' => 'sometimes|boolean',
            'cgst' => 'required|numeric|min:0|max:100',
            'sgst' => 'required|numeric|min:0|max:100',
        ]);

        $business = $this->currentBusiness();
        $business->update([
            'gst_number' => $request->input('gst_no'),
            'gst_enabled' => $request->boolean('gst_enabled') && $request->filled('gst_no'),
            'cgst' => floatval($request->input('cgst')),
            'sgst' => floatval($request->input('sgst')),
        ]);

        return redirect()->back()
            ->with('success', 'GST settings updated successfully!')
            ->with('active_tab', 'gst');
    }

    private function currentBusiness(): Business
    {
        $user = auth()->user();

        if ($user->business) {
            return $user->business;
        }

        $business = Business::create([
            'owner_user_id' => $user->id,
            'name' => 'EverythingEasy',
            'type' => 'restaurant',
            'email' => $user->email,
            'country' => 'India',
            'timezone' => 'Asia/Kolkata',
            'status' => 'active',
        ]);

        $user->update(['business_id' => $business->id]);

        return $business;
    }
}
