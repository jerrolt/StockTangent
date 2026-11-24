<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class InventoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $inventory = $this->route('inventory');

        return $inventory === null || Gate::allows('update', $inventory);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'organization_id' => $this->route('inventory') ? ['exclude'] : [
                'nullable',
                'integer',
                Rule::exists('organizations', 'id')->whereNull('deleted_at')
                    ->whereIn('id', $this->user()->organizations()->select('organizations.id')->toBase()),
            ],
        ];
    }
}
