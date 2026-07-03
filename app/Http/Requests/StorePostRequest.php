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
        // Define dependent validation constraints matching your React UI options
        $subCategoryMapping = [
            'a' => ['a1', 'a2', 'a3'],
            'b' => ['b1', 'b2', 'b3'],
            'c' => ['c1', 'c2', 'c3']
        ];

        // Safely extract allowed subcategories based on the current request's category
        $allowedSubCategories = isset($subCategoryMapping[$this->category])
            ? implode(',', $subCategoryMapping[$this->category])
            : '';

        return [
            'name'         => 'required|string|max:255',
            'price'        => 'required|numeric|min:0.01',
            'category'     => 'required|in:a,b,c',
            'sub_category' => 'required|string|in:' . $allowedSubCategories,

            // Validate that 'images' is a provided array with at least one item
            // 'images'       => 'required|array|min:1',
            // Validate each individual file inside the array
            // 'images.*'     => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
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
