<?php

namespace Easy\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $items_per_page_field = config('easy.input.pagination.items_per_page', 'itemsPerPage');
        $current_page_field = config('easy.input.pagination.current_page', 'page');
        return [
            $items_per_page_field => 'integer|min:1',
            $current_page_field => 'integer|min:1',
            'simple_pagination' => 'boolean',
            'sort_by' => 'string',
            'sort_asc' => 'boolean',
        ];
    }
}
