<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->route('id') || $this->method() === 'PUT' || ($this->method() === 'POST' && str_contains($this->route()->getName() ?? '', 'update'));
        $subCategoryMapping = [
            'a' => ['a1', 'a2', 'a3'],
            'b' => ['b1', 'b2', 'b3'],
            'c' => ['c1', 'c2', 'c3']
        ];
        $allowedSubCategories = isset($subCategoryMapping[$this->category])
            ? implode(',', $subCategoryMapping[$this->category])
            : '';

        return [
            'name'         => 'required|string|max:255',
            'price'        => 'required|numeric|min:0.01',
            'category'     => 'required|in:a,b,c',
            'sub_category' => 'required|string|in:' . $allowedSubCategories,
            'images'        => $isUpdate ? 'nullable|array' : 'required|array',
            'images.*'      => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'existing_images' => 'nullable'
        ];
    }

    /**
     * Custom error messages for specific validation failures.
     */
    public function messages(): array
    {
        return [
            'sub_category.in' => 'The selected sub category is invalid for the chosen category.',
            'images.*.image'  => 'Each uploaded file must be a valid image format.',
            'images.*.max'    => 'Images cannot be larger than 2MB.',
        ];
    }
}
