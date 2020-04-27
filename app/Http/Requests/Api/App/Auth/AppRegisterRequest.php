<?php

namespace App\Http\Requests\Api\App\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AppRegisterRequest extends FormRequest
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
            // 'name' => 'required|string|max:255',
            // 'email_id' => 'required|string|email|unique:taxi_admin|max:255',
            'password' => 'required|string|min:8',
            // 'mobile_no' => 'required',
        ];
    }
}
