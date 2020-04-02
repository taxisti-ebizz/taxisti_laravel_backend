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
        $input['lastupdated_date'] = date('Y-m-d');
        $input['lastupdated_time'] = date('H:m:s');
        $user = Admin::create($input); 
        
        $success['token'] =  $user->createToken('Texi_App')->accessToken; 
        $success['name'] =  $user->name;
         
        return response()->json([
            'success'    => true,
            'message'   => 'user date', 
            'data'    => $success,
        ], 200);
    }

}   