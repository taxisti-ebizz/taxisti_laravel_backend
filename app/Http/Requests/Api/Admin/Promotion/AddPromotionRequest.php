<?php

namespace App\Http\Requests\Api\Admin\Promotion;

use Illuminate\Foundation\Http\FormRequest;

class AddPromotionRequest extends FormRequest
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
            'type' => 'required',
            'code' => 'required|unique:taxi_promotion',
            'description' => 'required',
            'user_limit' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'promo_image' => 'required|image|mimes:jpeg,png,jpg'
        ];
    }
}
