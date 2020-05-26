<?php

namespace App\Http\Requests\Api\App\Driver;

use Illuminate\Foundation\Http\FormRequest;

class RequestActionRequest extends FormRequest
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
            'request_id' => 'required|exists:taxi_request,id',
            // 'driver_id' => 'required|exists:taxi_users,user_id',
            'status' => 'required',
        ];
    }
}
