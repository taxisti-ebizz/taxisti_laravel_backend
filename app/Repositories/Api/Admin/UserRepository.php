<?php


namespace App\Repositories\Api\Admin;

use File;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UserRepository extends Controller
{

    //get user list
    public function get_user_list($request)
    {
        $user_list = User::withCount([
                'complate_ride' => function ($query) {
                    $query->where('ride_status',3);
                }
            ])
            ->withCount([
                'cancel_ride' => function ($query) {
                    $query->where('is_canceled',1);
                    $query->where('cancel_by',2);
                }
            ])
            ->withCount([
                'total_review' => function ($query) {
                    $query->where('review_by','=','user');
                }
            ])
            ->withCount([
                'avg_rating' => function ($query) {
                    $query->select(DB::raw('coalesce(avg(ratting),0)'));
                }
            ])
            ->where('user_type',0)
            ->orderBy('user_id','DESC')
            ->paginate(10)->toArray();

        
        // add base url in profile_pic
        foreach($user_list['data'] as $user)
        {
            $user['profile_pic'] = $user['profile_pic'] != ''? env('AWS_S3_URL').$user['profile_pic'] : '';
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
            $user['profile_pic'] = $user['profile_pic'] != ''? env('AWS_S3_URL').$user['profile_pic'] : '';
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
            $imageName = 'uploads/users/'.time().'.'.$profile_pic->getClientOriginalExtension();
            $img = Storage::disk('s3')->put($imageName, file_get_contents($profile_pic), 'public');
            $input['profile_pic'] = $imageName;
                                  
        }

        // update data
        User::where('user_id',$request['user_id'])->update($input);
        
        // get user 
        $user = User::where('user_id',$request->user_id)->get()->first();
        $user['profile_pic'] = $user['profile_pic'] != ''? env('AWS_S3_URL').$user['profile_pic'] : '';


        return response()->json([
            'status'    => true,
            'message'   => 'update successfull', 
            'data'    => $user,
        ], 200);
        
    }

    // edit user status
    public function edit_user_status($request)
    {
        $input = $request->except(['user_id']);
        $input['updated_date'] = date('Y-m-d H:m:s');

        // update status
        User::where('user_id',$request['user_id'])->update($input);
        
        // get user details
        $get_user_detail = $this->get_user_detail($request);

        return response()->json([
            'status'    => true,
            'message'   => 'update successfull', 
            'data'    => $get_user_detail->original['data'],
        ], 200);
        
    }

    // delete user
    public function delete_user($request ,$user_id)
    {
        $user = User::where('user_id',$user_id)->first();
        $image_path = $user['profile_pic']; 

        // delete profile_pic
        Storage::disk('s3')->exists($user['profile_pic']) ? Storage::disk('s3')->delete($user['profile_pic']) : '';

        User::where('user_id',$user_id)->delete();
        
        return response()->json([
            'status'    => true,
            'message'   => 'user deleted', 
            'data'    => '',
        ], 200);   
    }

}   