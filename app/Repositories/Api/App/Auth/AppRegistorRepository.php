<?php


namespace App\Repositories\Api\App\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AppRegistorRepository extends Controller
{

    // user registor
    public function create($request)
    {


        $input['first_name'] = $request['first_name']; 
        $input['last_name'] = $request['last_name']; 
        $input['password'] = bcrypt($request['password']);
        $input['mobile_no'] = $request['phone']; 
        $input['date_of_birth'] = $request['dob']; 
        $input['login_type'] = $request['login_type']; 
        $input['user_type'] = $request['user_type']; 
        $input['facebook_id'] = $request['facebook_id'] != ''?$request['facebook_id']:''; 
        $input['device_type'] = $request['device_type']; 
        $input['device_token'] = $request['device_token']; 
        $input['status'] = 0; 
        $input['verify'] = 1; 

        // profile_pic handling 
        if ($request->file('profile_pic')) {

            $profile_pic = $request->file('profile_pic');
            $imageName = 'uploads/users/' . time() . '.' . $profile_pic->getClientOriginalExtension();
            $img = Storage::disk('s3')->put($imageName, file_get_contents($profile_pic), 'public');
            $input['profile_pic'] = $imageName;
        }

        $user = User::create($input); 
        
        $success['token'] =  $user->createToken('Texi_App')->accessToken; 
        $success['name'] =  User::where('user_id',$user->user_id)->first();
         
        return response()->json([
            'success'    => true,
            'message'   => 'user date', 
            'data'    => $success,
        ], 200);
    }

}   