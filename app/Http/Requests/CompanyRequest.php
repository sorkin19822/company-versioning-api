<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Trim whitespace from string inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(
            collect($this->only(['name', 'edrpou', 'address']))
                ->map(fn ($value) => is_string($value) ? trim($value) : $value)
                ->all()
        );
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'min:2', 'max:256'],
            // No unique rule: endpoint is upsert (update or create by edrpou).
            // Uniqueness is enforced at the DB level by a unique index on companies.edrpou.
            'edrpou'  => ['required', 'digits_between:1,10'],
            'address' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'edrpou.digits_between' => 'The EDRPOU code must contain digits only (1–10 digits).',
            'name.min'              => 'The company name must be at least 2 characters.',
            'name.max'              => 'The company name must not exceed 256 characters.',
        ];
    }
}
