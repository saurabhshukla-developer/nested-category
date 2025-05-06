<?php

namespace App\Http\Requests;

use App\Enums\CategoryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) {
                    $parentId = $this->input('parent_id');
                    if (is_null($parentId)) {
                        $query->whereNull('parent_id');
                    } else {
                        $query->where('parent_id', $parentId);
                    }
                }),
            ],
            'status' => ['required', new Enum(CategoryStatus::class)],
            'parent_id' => 'nullable|exists:categories,id',
        ];
    }
}
