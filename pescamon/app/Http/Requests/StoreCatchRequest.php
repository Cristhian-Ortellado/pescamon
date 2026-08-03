<?php

namespace App\Http\Requests;

use App\Models\Species;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'species_id' => ['required', 'integer', Rule::exists('species', 'id')->where('is_active', true)],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            'weight_kg' => ['required', 'numeric', 'min:0.001', 'max:2000'],
            'length_cm' => ['nullable', 'numeric', 'min:0.1', 'max:1000'],
            'caught_at' => ['required', 'date', 'before_or_equal:now'],
            'method' => ['required', Rule::in(['spinning', 'fly', 'bait_casting', 'trolling', 'other'])],
            'water_body_type' => ['required', Rule::in(['river', 'lake', 'sea', 'reservoir', 'other'])],
            'bait_used' => ['nullable', 'string', 'max:100'],
            'location_name' => ['nullable', 'string', 'max:150'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $speciesId = $this->input('species_id');
            $weightKg = (float) $this->input('weight_kg');

            if (! $speciesId) {
                return;
            }

            $species = Species::find($speciesId);

            if (! $species || ! $species->average_weight_kg) {
                return;
            }

            // Reject catches more than 10× the species average (likely data entry error)
//            $maxAllowed = (float) $species->average_weight_kg * 10;
//
//            if ($weightKg > $maxAllowed) {
//                $validator->errors()->add(
//                    'weight_kg',
//                    "El peso máximo permitido para {$species->name} es {$maxAllowed} kg."
//                );
//            }
        });
    }

    public function messages(): array
    {
        return [
            'species_id.exists' => 'La especie seleccionada no existe o no está activa.',
            'image.required' => 'La foto de la captura es obligatoria.',
            'image.mimes' => 'La imagen debe ser JPG o PNG.',
            'image.max' => 'La imagen no puede superar los 10 MB.',
            'weight_kg.required' => 'El peso es obligatorio.',
            'weight_kg.min' => 'El peso debe ser mayor a 0.',
            'caught_at.before_or_equal' => 'La fecha de captura no puede ser en el futuro.',
        ];
    }
}
