<?php

namespace App\Http\Requests\Api\App\Rider;

use Illuminate\Foundation\Http\FormRequest;

class RequestRideRequest extends FormRequest
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
            'rider_id' => 'required|exists:taxi_users,user_id',
            'driver_id' => 'required',
            'start_location' => 'required',
            'start_latitude' => 'required',
            'start_longitude' => 'required',
            'end_location' => 'required',
            'end_latitude' => 'required',
            'end_longitude' => 'required',
            // 'note' => 'required',
            'passengers' => 'required',
            'amount' => 'required',
            'distance' => 'required',

        ];
    }
}
