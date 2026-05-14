<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePage extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Page::class);
    }

    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255'],
            'content'          => ['required', 'string'],
            'content_json'     => ['nullable', 'array'],
            'excerpt'          => ['nullable', 'string', 'max:1000'],
            'status'           => ['required', Rule::enum(ContentStatus::class)],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'meta_keywords'    => ['nullable', 'string', 'max:255'],
            'og_title'         => ['nullable', 'string', 'max:255'],
            'og_description'   => ['nullable', 'string', 'max:500'],
            'published_at'     => ['nullable', 'date'],
            'parent_id'        => ['nullable', 'integer', Rule::exists('pages', 'id')],
        ];
    }
}
