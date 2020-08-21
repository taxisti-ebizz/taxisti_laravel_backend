<?php

namespace App\Http\Requests\Api\App\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AppLoginRequest extends FormRequest
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
            // 'email_id' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
            'phone' => 'required|exists:taxi_users,mobile_no',
            'user_type' => 'required',
            'device_type' => 'required',
            'device_token' => 'required',

        ];
    }
}   
