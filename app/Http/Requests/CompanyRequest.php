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
        $this->merge([
            'name'    => is_string($this->name)    ? trim($this->name)    : $this->name,
            'edrpou'  => is_string($this->edrpou)  ? trim($this->edrpou)  : $this->edrpou,
            'address' => is_string($this->address) ? trim($this->address) : $this->address,
        ]);
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
