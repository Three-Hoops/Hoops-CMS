<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role->canEdit();
    }

    public function rules(): array
    {
        return [
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['required', 'integer'],
            'action' => ['required', 'string'],
        ];
    }
}
