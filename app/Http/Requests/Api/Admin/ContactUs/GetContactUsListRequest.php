<?php

namespace App\Http\Requests\Api\Admin\ContactUs;

use Illuminate\Foundation\Http\FormRequest;

class GetContactUsListRequest extends FormRequest
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
            //
        ];
    }
}
