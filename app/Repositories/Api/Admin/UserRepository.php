<?php


namespace App\Repositories\Api\Admin;

use File;
use ArrayObject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UserRepository extends Controller
{

    //get user list
    public function get_user_list($request)
    { 
        $user_list = array();

        if($request['type'] == 'currentWeek')
        {
            $list = 'CurrentWeek';

            $previous_week = strtotime("0 week +1 day");
            $start_week = strtotime("last saturday midnight",$previous_week);
            $end_week = strtotime("next friday",$start_week);
            $start_current_week = date("Y-m-d H:i:s",$start_week);
            $end_current_week = date("Y-m-d 23:59:00",$end_week);

            $user_list = User::withCount([
                'complate_ride' => function ($query) {
                    $query->where('ride_status', 3);
                }
            ])
            ->withCount([
                'cancel_ride' => function ($query) {
                    $query->where('is_canceled', 1);
                    $query->where('cancel_by', 2);
                }
            ])
            ->where('user_type', 0)
            ->whereBetween('created_date', [$start_current_week, $end_current_week])
            ->orderBy('user_id', 'DESC')
            ->paginate(10)->toArray();
        }
        elseif($request['type'] == 'lastWeek')
        {
            $list = 'LastWeek';

            $previous_week1 = strtotime("-1 week +1 day");
            $start_week = strtotime("last saturday midnight",$previous_week1);
            $end_week = strtotime("next friday",$start_week);
            $start_last_week = date("Y-m-d H:i:s",$start_week);
            $end_last_week = date("Y-m-d 23:59:00",$end_week);

            $user_list = User::withCount([
                'complate_ride' => function ($query) {
                    $query->where('ride_status', 3);
                }
            ])
            ->withCount([
                'cancel_ride' => function ($query) {
                    $query->where('is_canceled', 1);
                    $query->where('cancel_by', 2);
                }
            ])
            ->where('user_type', 0)
            ->whereBetween('created_date', [$start_last_week, $end_last_week])
            ->orderBy('user_id', 'DESC')
            ->paginate(10)->toArray();
        }
        else{
            
            $list = 'All';
            $user_list = User::withCount([
                'complate_ride' => function ($query) {
                    $query->where('ride_status', 3);
                }
            ])
            ->withCount([
                'cancel_ride' => function ($query) {
                    $query->where('is_canceled', 1);
                    $query->where('cancel_by', 2);
                }
            ])
            ->where('user_type', 0)
            ->orderBy('user_id', 'DESC')
            ->paginate(10)->toArray();
        }

        if($user_list['data'])
        {
            // add base url in profile_pic
            foreach ($user_list['data'] as $user) {
                $user['profile_pic'] = $user['profile_pic'] != '' ? env('AWS_S3_URL') . $user['profile_pic'] : '';

                $ratting_review = $this->user_ratting_review($user['user_id']);
                $user['total_review_count'] = $ratting_review->total_review;
                $user['avg_rating_count'] = $ratting_review->avg_ratting;

                $data[] = $user;
            }
            $user_list['data'] = $data;

            return response()->json([
                'status'    => true,
                'message'   => $list.' user list',
                'data'    => $user_list,
            ], 200);

        } 
        else {
            return response()->json([
                'status'    => false,
                'message'   => 'No user found',
                'data'    => new ArrayObject,
            ], 200);
        }
    }

    // get user detail
    public function get_user_detail($request)
    {
        $user = User::where('user_id', $request->user_id)->first();
        if ($user) {
            $user['profile_pic'] = $user['profile_pic'] != '' ? env('AWS_S3_URL') . $user['profile_pic'] : '';
            return response()->json([
                'status'    => true,
                'message'   => 'user detail',
                'data'    => $user,
            ], 200);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'No user found',
                'data'    => new ArrayObject,
            ], 200);
        }
    }

    // edit user detail
    public function edit_user_detail($request)
    {
        $input = $request->except(['user_id']);
        $input['updated_date'] = date('Y-m-d H:i:s');

        // profile_pic handling 
        if ($request->file('profile_pic')) {

            $profile_pic = $request->file('profile_pic');
            $imageName = 'uploads/users/' . time() . '.' . $profile_pic->getClientOriginalExtension();
            $img = Storage::disk('s3')->put($imageName, file_get_contents($profile_pic), 'public');
            $input['profile_pic'] = $imageName;
        }

        // update data
        User::where('user_id', $request['user_id'])->update($input);

        // get user 
        $user = User::where('user_id', $request->user_id)->get()->first();
        $user['profile_pic'] = $user['profile_pic'] != '' ? env('AWS_S3_URL') . $user['profile_pic'] : '';


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
        $input['updated_date'] = date('Y-m-d H:i:s');

        // update status
        User::where('user_id', $request['user_id'])->update($input);

        // get user details
        $get_user_detail = $this->get_user_detail($request);

        return response()->json([
            'status'    => true,
            'message'   => 'update successfull',
            'data'    => $get_user_detail->original['data'],
        ], 200);
    }

    // delete user
    public function delete_user($request, $user_id)
    {
        $user = User::where('user_id', $user_id)->first();
        $image_path = $user['profile_pic'];

        // delete profile_pic
        Storage::disk('s3')->exists($user['profile_pic']) ? Storage::disk('s3')->delete($user['profile_pic']) : '';

        User::where('user_id', $user_id)->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'user deleted',
            'data'    => '',
        ], 200);
    }

    // get rider reviews
    public function get_rider_reviews($request)
    {
        $rider_reviews = User::select(
            DB::raw('CONCAT(first_name," ",last_name) as rider_name'),
            'mobile_no as rider_mobile','user_id as rider_id'
        )
        ->where('user_type', 0)
        ->orderBy('user_id', 'DESC')
        ->paginate(10)->toArray();

        if ($rider_reviews['data']) {

            foreach ($rider_reviews['data'] as $user) {
    
                $ratting_review = $this->user_ratting_review($user['rider_id']);
                $user['total_review_count'] = $ratting_review->total_review;
                $user['avg_rating_count'] = $ratting_review->avg_ratting;
    
                $data[] = $user;
            }
            $rider_reviews['data'] = $data;
    
            return response()->json([
                'status'    => true,
                'message'   => 'Rider reviews',
                'data'    => $rider_reviews,
            ], 200);
        } else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available',
                'data'    => new ArrayObject,
            ], 200);
        }
    }


    // view rider reviews
    public function view_rider_reviews($request)
    {
        $rider_reviews = DB::table('taxi_ratting')
            ->select('taxi_ratting.*','driver_id','rider_id',
                DB::raw('CONCAT(taxi_users.first_name," ",taxi_users.last_name) as driver_name'),
                'taxi_users.mobile_no as driver_mobile' 
            )
            ->join('taxi_request','taxi_ratting.request_id','taxi_request.id')
            ->join('taxi_users','taxi_request.rider_id','taxi_users.user_id')
            ->where('taxi_request.rider_id',$request->rider_id)
            ->where('review_by','driver')
            ->get();

        if($rider_reviews)
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Rider reviews', 
                'data'    => $rider_reviews,
            ], 200);   
        }
        else {
            return response()->json([
                'status'    => true,
                'message'   => 'No data available', 
                'data'    => array(),
            ], 200);
        }

    }

    //get specific user list
    public function get_specific_user_list($request)
    {
        $user_list = User::select('user_id as id',
            DB::raw('CONCAT(first_name," ",last_name) as user_name'),
            'user_type','mobile_no as mobile'
        )
        ->where('verify', 1)
        ->orderBy('user_id', 'DESC')
        ->paginate(10)->toArray();


        return response()->json([
            'status'    => true,
            'message'   => 'All user list',
            'data'    => $user_list,
        ], 200);
    }

    

    // Sub Function

    // user ratting review
    public function user_ratting_review($user_id)
    {
        $ratting_review = DB::table('taxi_ratting')->select(
                DB::raw('count(taxi_ratting.id) as total_review, ROUND(coalesce(avg(ratting),0),1) as avg_ratting')
            )
            ->join('taxi_request','taxi_ratting.request_id','taxi_request.id')
            ->where('taxi_request.rider_id',$user_id)
            ->where('taxi_ratting.review_by','driver')
            ->first();

        return $ratting_review;

    }

    
}
