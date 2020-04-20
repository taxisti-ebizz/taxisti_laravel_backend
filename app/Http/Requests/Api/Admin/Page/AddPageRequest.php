<?php

namespace App\Http\Requests\Api\Admin\Page;

use Illuminate\Foundation\Http\FormRequest;

class AddPageRequest extends FormRequest
{
    public $validator = null;
    protected function failedValidation($validator)
    {
        $this->validator = $validator;
    }
    
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
            'page_title' => 'required',
            'page_slug' => 'required',
            'content' => 'required',
        ];
    }
}
