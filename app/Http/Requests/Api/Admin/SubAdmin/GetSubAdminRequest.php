<?php

namespace App\Http\Requests\Api\Admin\SubAdmin;

use Illuminate\Foundation\Http\FormRequest;

class GetSubAdminRequest extends FormRequest
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
            'user_id' => 'required|exists:taxi_admin,user_id'
        ];
    }
}
