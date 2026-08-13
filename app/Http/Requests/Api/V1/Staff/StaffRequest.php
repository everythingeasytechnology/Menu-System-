<?php

namespace App\Http\Requests\Api\V1\Staff;

use App\Http\Requests\Api\V1\ApiFormRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $staffId = $this->route('staff')?->id;
        $isCreating = $this->isMethod('post') && ! $this->route('staff');

        return [
            'name' => [$isCreating ? 'required' : 'sometimes', 'string', 'max:255'],
            'email' => [
                $isCreating ? 'required' : 'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staffId),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => [$isCreating ? 'required' : 'sometimes', 'string', Rule::in([
                'admin',
                'manager',
                'waiter',
                'kitchen_staff',
                'cashier',
            ])],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive', 'suspended'])],
            'password' => [$isCreating ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'remove_profile_image' => ['sometimes', 'boolean'],
        ];
    }
}
