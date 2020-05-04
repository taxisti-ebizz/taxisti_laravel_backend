<?php

namespace App\Http\Requests\Api\App\Common;

use Illuminate\Foundation\Http\FormRequest;

class AddReviewRequest extends FormRequest
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
            'review_type' => 'required|in:byrider,bydriver',
            'request_id' => 'required|exists:taxi_request,id',
            'driver_id' => 'required|exists:taxi_users,user_id',
            'rider_id' => 'required|exists:taxi_users,user_id',
            'ratting' => 'required',
            'review' => 'required',
            
        ];
    }
}
