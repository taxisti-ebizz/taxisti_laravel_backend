<?php


namespace App\Repositories\Api\App\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Api\App\AppCommonRepository;

class AppRegistorRepository extends Controller
{
    protected $appCommon;

    public function __construct()
    {
        $this->appCommon = new AppCommonRepository;
    }

    // user registor
    public function create($request)
    {
        date_default_timezone_set("Africa/Tripoli");

        $input['first_name'] = $request['first_name']; 
        $input['last_name'] = $request['last_name']; 
        $input['password'] = md5($request['password']);
        $input['mobile_no'] = $request['phone']; 
        $input['date_of_birth'] = isset($request['dob']) ? $request['dob'] : '0000-00-00'; 
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

        $user = User::where('mobile_no',$request['phone'])->first();
        if($user)
        {
            // delete files
            Storage::disk('s3')->exists($user->profile_pic) ? Storage::disk('s3')->delete($user->profile_pic) : '';

            // update user data
            $input['updated_date'] =  date('Y-m-d H:i:s');
            User::where('mobile_no',$request['phone'])->update($input); 
        }
        else {

            // create user
            $input['created_date'] =  date('Y-m-d H:i:s');
            User::create($input); 
        }

        $user = User::where('mobile_no',$request['phone'])->first();

        Auth::login($user);

        $user->profile_pic = $user->profile_pic != '' ? env('AWS_S3_URL').$user->profile_pic : '';
        $user->date_of_birth = $user->date_of_birth != '' ? $user->date_of_birth : '';

        $success = $user; 
        $success['token'] =  "Bearer ".$user->createToken('Texi_App')->accessToken; 
         
        return response()->json([
            'status'    => true,
            'message'   => 'user date', 
            'data'    => $success,
        ], 200);
    }

}   