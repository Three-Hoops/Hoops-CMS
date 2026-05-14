<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePost extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('post'));
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
            'published_at'     => ['nullable', 'date'],
            'featured_image'   => ['nullable', 'string', 'max:255'],
            'category_id'      => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'tag_ids'          => ['nullable', 'array'],
            'tag_ids.*'        => ['integer', Rule::exists('tags', 'id')],
            'is_featured'      => ['boolean'],
        ];
    }
}
