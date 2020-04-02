<?php

namespace App\Http\Controllers\Api\Admin\Auth;

use App\Models\Admin;
use phpseclib\Crypt\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\Api\Admin\Auth\AdminLoginRequest;

class AdminLoginController extends Controller
{
    public $successStatus = 200;
    public $validator = null;
    
    protected function failedValidation($validator)
    {
        $this->validator = $validator;
    }
    
    /** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function login(AdminLoginRequest $request)
    { 
        if ($request->validator->fails()) {
            return response()->json([
                'success'    => false,
                'message'   => 'Data is invalid.', 
                'errors'    => $request->validator->errors(),
            ], 400);
        }   

        if($admin = Admin::where(['email_id' => $request->email_id,'password' => md5($request->password)])->first()){ 

            Auth::login($admin);
            $success['token'] =  $admin->createToken('Texi_App')->accessToken; 
            $success['user'] =  $admin; 
            
            return response()->json([
                'success'    => true,
                'message'   => 'Login success.', 
                'data'    => $success,
            ], 200);
        } 
        else{ 
            return response()->json([
                'success'    => false,
                'message'   => 'Fail login', 
                'data'    => array(),
            ], 401);
        } 
    }
}
