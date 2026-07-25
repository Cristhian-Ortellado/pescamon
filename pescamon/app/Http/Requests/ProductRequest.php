<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Access is already gated by the `superadmin` route middleware.
        return true;
    }

    public function rules(): array
    {
        // Image is required when creating, optional when editing (keep current).
        $imageRule = $this->isMethod('post')
            ? ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240']
            : ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'];

        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'is_active' => ['required', 'boolean'],
            'image' => $imageRule,
        ];
    }

    protected function prepareForValidation(): void
    {
        // Checkboxes/toggles arrive as "1"/"0" or true/false from the form.
        $this->merge([
            'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
