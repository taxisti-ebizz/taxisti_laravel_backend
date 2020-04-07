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
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        if($admin = Admin::where(['email_id' => $request->email_id,'password' => md5($request->password)])->first()){ 

            Auth::login($admin);
            $success =  Admin::where('user_id',$admin->user_id)->get(['name','email_id','mobile_no'])->first();
            $success['token'] =  $admin->createToken('Texi_App')->accessToken; 
            
            return response()->json([
                'status'    => true,
                'message'   => 'Login successfully', 
                'data'    => $success,
            ], 200);
        } 
        else{ 
            return response()->json([
                'status'    => false,
                'message'   => "email and password don't match", 
                'errors'    => '',
            ], 200);
        } 
    }
}
