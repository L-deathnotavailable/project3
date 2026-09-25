<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, string|ValidationRule>> */
    public function rules(): array
    {
        $presence = $this->isMethod('put') ? 'required' : 'sometimes';

        return [
            'name' => [
                $presence,
                'string',
                'max:50',
                Rule::unique('tags', 'name')->ignore($this->route('tag')),
            ],
        ];
    }
}
