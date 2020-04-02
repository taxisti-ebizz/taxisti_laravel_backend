<?php


namespace App\Repositories\Api\Admin\Auth;

use App\Models\User;
use App\Models\Admin;
use App\Http\Controllers\Controller;

class AdminRegistorRepository extends Controller
{



    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $request
     * @return \App\Models\User
     */
    public function create($request)
    {

        $input = $request->all(); 
        $input['password'] = md5($input['password']); 
        $input['type'] = 1;
        $input['status'] = 1;
        $input['lastupdated_date'] = date('Y-m-d');
        $input['lastupdated_time'] = date('H:m:s');
        $admin = Admin::create($input); 
        
        $success['token'] =  $admin->createToken('Texi_App')->accessToken; 
        $success['data'] =  $admin;
         
        return response()->json([
            'success'    => true,
            'message'   => 'Registration successfully', 
            'data'    => $success,
        ], 200);
    }

}   