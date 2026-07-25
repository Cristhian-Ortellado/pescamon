<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\Validator;
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
        $isCreate = $this->isMethod('post') && ! $this->routeIsUpdate();

        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'is_active' => ['required', 'boolean'],
            // At least one image is required when creating; on edit new images
            // are optional additions.
            'images' => [$isCreate ? 'required' : 'nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'removed_image_ids' => ['nullable', 'array'],
            'removed_image_ids.*' => ['integer'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Toggles/booleans arrive as "1"/"0" or true/false from the form.
        $this->merge([
            'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $product = $this->route('product');

            if (! $product instanceof Product) {
                return;
            }

            // Ensure an edited product never ends up with zero images.
            $removed = collect($this->input('removed_image_ids', []))->map('intval');
            $remaining = $product->images()->whereNotIn('id', $removed)->count();
            $added = count($this->file('images', []));

            if ($remaining + $added < 1) {
                $validator->errors()->add('images', 'El producto debe tener al menos una imagen.');
            }
        });
    }

    /**
     * True when this request targets the resource update route (which uses
     * POST + method spoofing for multipart uploads).
     */
    private function routeIsUpdate(): bool
    {
        return $this->route('product') !== null;
    }
}
