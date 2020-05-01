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
        date_default_timezone_set("Africa/Tripoli");

        $input['first_name'] = $request['first_name']; 
        $input['last_name'] = $request['last_name']; 
        $input['password'] = md5($request['password']);
        $input['mobile_no'] = $request['phone']; 
        $input['date_of_birth'] = $request['dob']; 
        $input['login_type'] = $request['login_type']; 
        $input['user_type'] = $request['user_type']; 
        $input['facebook_id'] = $request['facebook_id'] != ''?$request['facebook_id']:''; 
        $input['device_type'] = $request['device_type']; 
        $input['device_token'] = $request['device_token']; 
        $input['status'] = 0; 
        $input['verify'] = 1; 
        $imageName = '';

        // profile_pic handling 
        if ($request->file('profile_pic')) {

            $profile_pic = $request->file('profile_pic');
            $imageName = 'uploads/users/' . time() . '.' . $profile_pic->getClientOriginalExtension();
            $img = Storage::disk('s3')->put($imageName, file_get_contents($profile_pic), 'public');
            $input['profile_pic'] = $imageName;
        }

        $user_data = User::where('mobile_no',$request['phone'])->first();
        if($user_data)
        {
            // delete files
            Storage::disk('s3')->exists($user_data->profile_pic) ? Storage::disk('s3')->delete($user_data->profile_pic) : '';

            // update user data
            $user = User::where('mobile_no',$request['phone'])->update($input); 
        }
        else {

            // create user
            $user = User::create($input); 
        }

        $user_data = User::where('mobile_no',$request['phone'])->first();
        $user_data->profile_pic = $user_data->profile_pic != '' ? env('AWS_S3_URL').$user_data->profile_pic : '';
        
        $success['token'] =  $user_data->createToken('Texi_App')->accessToken; 
        $success['data'] = $user_data; 
         
        return response()->json([
            'success'    => true,
            'message'   => 'user date', 
            'data'    => $success,
        ], 200);
    }

}   