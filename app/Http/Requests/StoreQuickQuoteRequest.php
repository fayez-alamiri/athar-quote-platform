<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuickQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * SRS Validation Rule for Quick Quotes:
     * - Quote text only, 3 to 155 characters.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quote_text' => ['required', 'string', 'min:3', 'max:155'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('quote_text')) {
            $this->merge([
                'quote_text' => trim((string) $this->input('quote_text')),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'quote_text.required' => __('Please enter the quote text.'),
            'quote_text.min' => __('Quick quote text must be between 3 and 155 characters.'),
            'quote_text.max' => __('Quick quote text must be between 3 and 155 characters.'),
        ];
    }
}