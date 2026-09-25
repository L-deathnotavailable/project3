<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('note')) ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        $presence = $this->isMethod('put') ? 'required' : 'sometimes';

        return [
            'text' => [$presence, 'string'],
            'tag_id' => [$presence, 'integer', 'exists:tags,id'],
        ];
    }
}
