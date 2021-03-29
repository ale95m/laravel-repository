<?php

namespace ale95m\Easy\Http\Requests;

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
        return [
            'itemsPerPage'=>'integer|min:1',
            'page'=>'integer|min:1',
            'simple_pagination'=>'boolean',
            'sort_by'=>'string',
            'sort_asc'=>'boolean',
        ];
    }
}
