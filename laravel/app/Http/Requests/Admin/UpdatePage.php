<?php

namespace App\Http\Requests\Admin;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Closure;
use App\Models\Page;

class UpdatePage extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('page'));
    }

    public function rules(): array
    {
        $pageId = $this->route('page')->id;

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
            'parent_id'        => ['nullable', 'integer', Rule::exists('pages', 'id'), function (string $attribute, mixed $value, Closure $fail) {
                $page = $this->route('page');
                if ($value === null) {
                    return;
                }
                if ((int) $value === $page->id) {
                    $fail('A page cannot be its own parent.');
                    return;
                }
                $candidate = Page::find($value);
                if ($candidate && $candidate->isDescendantOf($page)) {
                    $fail('A page cannot be assigned a descendant as its parent.');
                }
            }]
        ];
    }
}
