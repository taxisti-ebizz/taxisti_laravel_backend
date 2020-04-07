<?php


namespace App\Repositories\Api\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use File;

class UserRepository extends Controller
{

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $request
     * @return \App\Models\User
     */
    public function get_user_list($request)
    {
        $user_list = User::paginate(15)->toArray();
        // add base url in profile_pic
        foreach($user_list['data'] as $user)
        {

            $user['profile_pic'] = $user['profile_pic'] != ''? url($user['profile_pic']) : '';
            $data[] = $user;

        }
        $user_list['data'] = $data; 
        return response()->json([
            'status'    => true,
            'message'   => 'All user list', 
            'data'    => $user_list,
        ], 200);
    }

    // get user detail
    public function get_user_detail($request)
    {
        $user = User::where('user_id',$request->user_id)->first();
        if($user)
        {
            $user['profile_pic'] = $user['profile_pic'] != ''? url($user['profile_pic']) : '';
            return response()->json([
                'status'    => true,
                'message'   => 'user detail', 
                'data'    => $user,
            ], 200);
        }
        else {
            return response()->json([
                'status'    => false,
                'message'   => 'No user found', 
                'error'    => '',
            ], 200);
        }
    }

    // edit user detail
    public function edit_user_detail($request)
    {
        $input = $request->except(['user_id']);
        $input['updated_date'] = date('Y-m-d H:m:s');

        // profile_pic handling 
        if($request->file('profile_pic')){

            $profile_pic = $request->file('profile_pic');
            $fileName = 'public/uploads/users/'.time().'.'.$profile_pic->getClientOriginalExtension();  
            
            // move profile_pic in destination
            if($profile_pic->move(public_path('/uploads/users/'), $fileName))
            {
                $input['profile_pic'] = $fileName;
            }
            else {
                return response()->json([
                    'status'    => false,
                    'message'   => 'fail to move profile_pic', 
                    'error'    => '',
                ], 200);
            }
                                  
        }

        // update data
        User::where('user_id',$request['user_id'])->update($input);
        
        // get user 
        $user = User::where('user_id',$request->user_id)->get()->first();
        $user['profile_pic'] = $user['profile_pic'] != ''? url($user['profile_pic']) : '';


        return response()->json([
            'status'    => true,
            'message'   => 'update successfull', 
            'data'    => $user,
        ], 200);
        
    }

    // delete user
    public function delete_user($request ,$user_id)
    {
        $user = User::where('user_id',$user_id)->first();
        $image_path = $user['profile_pic']; 

        // delete profile_pic
        if(File::exists($image_path))
        {
            File::delete($image_path);
        }

        User::where('user_id',$user_id)->delete();
        
        return response()->json([
            'status'    => true,
            'message'   => 'user deleted', 
            'data'    => '',
        ], 200);   
    }

}   