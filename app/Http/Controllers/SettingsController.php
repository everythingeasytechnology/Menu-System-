<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
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
        $business = BusinessSetting::firstOrCreate([], [
            'brand_name' => 'EverythingEasy',
            'business_email' => 'contact@everythingeasy.com',
            'shop_no' => 'G-12',
            'address' => 'Connaught Place, Block C',
            'country' => 'India',
            'state' => 'Delhi',
            'district' => 'New Delhi',
            'pincode' => '110001',
            'gst_no' => '07AAAAA1111A1Z1',
        ]);

        $razorpay = RazorpaySetting::firstOrCreate([], [
            'enabled' => false,
            'key_id' => '',
            'key_secret' => '',
        ]);

        $cash = CashSetting::firstOrCreate([], [
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
            'gst_no' => 'nullable|string|max:50',
        ]);

        $business = BusinessSetting::firstOrCreate([]);
        
        $updateData = $validated;
        unset($updateData['logo']); // Remove the logo file instance from mass-assignment data

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
            'key_secret' => 'required_if:enabled,1|nullable|string|max:255',
        ]);

        $razorpay = RazorpaySetting::firstOrCreate([]);
        $razorpay->update([
            'enabled' => $request->has('enabled'),
            'key_id' => $request->key_id,
            'key_secret' => $request->key_secret,
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
        $cash = CashSetting::firstOrCreate([]);
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
}
