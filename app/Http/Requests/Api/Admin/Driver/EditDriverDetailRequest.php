<?php

namespace App\Http\Requests\Api\Admin\Driver;

use Illuminate\Foundation\Http\FormRequest;

class EditDriverDetailRequest extends FormRequest
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
            'driver_id' => 'required|exists:taxi_users,user_id',
            'first_name' => 'max:255',
            'last_name' => 'max:255',
            // 'profile_pic' => 'mimes:jpeg,jpg,png',
        ];
    }
}
