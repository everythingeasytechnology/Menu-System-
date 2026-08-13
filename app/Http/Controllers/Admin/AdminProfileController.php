<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', [
            'admin' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $admin = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'remove_profile_image' => ['sometimes', 'boolean'],
        ]);

        unset($data['profile_image'], $data['remove_profile_image']);

        if ($request->hasFile('profile_image')) {
            $this->deleteProfileImage($admin->profile_image_path);
            $data['profile_image_path'] = $request->file('profile_image')->store('profile-images', 'public');
        } elseif ($request->boolean('remove_profile_image')) {
            $this->deleteProfileImage($admin->profile_image_path);
            $data['profile_image_path'] = null;
        }

        $admin->update($data);

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $request->user()->password)) {
            return redirect()
                ->route('admin.profile.edit')
                ->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $request->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', 'Password updated.');
    }

    private function deleteProfileImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
