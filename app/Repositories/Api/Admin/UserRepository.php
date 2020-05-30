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

            $user_list = User::withCount('complate_ride','cancel_ride','total_review')
            ->withCount([
                'avg_rating' => function ($query) {
                    $query->select(DB::raw('ROUND(coalesce(avg(ratting),0),1)'));
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

            $user_list = User::withCount('complate_ride','cancel_ride','total_review')
            ->withCount([
                'avg_rating' => function ($query) {
                    $query->select(DB::raw('ROUND(coalesce(avg(ratting),0),1)'));
                }
            ])
            ->where('user_type', 0)
            ->whereBetween('created_date', [$start_last_week, $end_last_week])
            ->orderBy('user_id', 'DESC')
            ->paginate(10)->toArray();
        }
        elseif($request['type'] == 'filter')
        {
            if(isset($request['filter']))
            {

                $list = 'Filter';
                $query = User::withCount('complate_ride','cancel_ride','total_review')
                ->withCount([
                    'avg_rating' => function ($query) {
                        $query->select(DB::raw('ROUND(coalesce(avg(ratting),0),1)'));
                    }
                ])
                ->where('user_type', 0);
                
                $where = [];
                $filter = json_decode($request['filter']);

                if(!empty($filter->username)) // username filter
                {
                    $username = explode(' ',$filter->username);
                    $where['first_name'] = $username[0];
                    isset($username[1]) ? $where['last_name'] = $username[1] : ''; 
                    
                    $query->where($where);
                }
                if(!empty($filter->mobile)) // mobile filter 
                {
                    $where['mobile_no'] = $filter->mobile;
                    $query->where($where);
                }
                if(!empty($filter->dob)) // date_of_birth filter
                {
                    $query->whereBetween('date_of_birth',explode(' ',$filter->dob));
                }
                if(!empty($filter->dor)) // date_of_register
                {
                    $query->whereBetween('created_date',explode(' ',$filter->dor));
                }
                if(!empty($filter->device_type)) // device_type filter
                {
                    $device_type = explode(',',$filter->device_type);
                    if(count($device_type) > 1)
                    {
                        $query->whereBetween('device_type',$device_type);
                    }
                    else
                    {
                        $query->where('device_type',$device_type[0]);
                    }
                }
                if(!empty($filter->verify)) // verify filter
                {
                    
                    $verify = explode(',',$filter->verify);
                    if(count($verify) > 1)
                    {
                        $query->whereBetween('verify',[0,1]);
                    }
                    else
                    {
                        $verify = $verify[0] == 2 ? 0 : 1; 
                        $query->where('verify',$verify);
                    }
                }
                if(!empty($filter->complete_ride)) // complete_ride filter
                {
                    $complete_ride = explode('-',$filter->complete_ride);
                    $query = $query->where(function($q) use ( $complete_ride ){
                        $q->has('complate_ride','>=',$complete_ride[0]);
                        $q->has('complate_ride','<=',$complete_ride[1]);
                    });

                }
                if(!empty($filter->cancelled_ride)) // cancel_ride filter
                {
                    $cancel_ride = explode('-',$filter->cancelled_ride);
                    $query = $query->where(function($q) use ( $cancel_ride ){
                        $q->has('cancel_ride','>=',$cancel_ride[0]);
                        $q->has('cancel_ride','<=',$cancel_ride[1]);
                    });
                }
                if(!empty($filter->total_review)) // total_review filter
                {
                    $total_review = explode('-',$filter->total_review);
                    $query = $query->where(function($q) use ( $total_review ){
                        $q->has('total_review','>=',$total_review[0]);
                        $q->has('total_review','<=',$total_review[1]);
                    });

                }
                if(!empty($filter->average_ratting)) // average_ratting filter
                {
                    $average_ratting = explode('-',$filter->average_ratting);
                    $query = $query->where(function($q) use ( $average_ratting ){
                        $q->has('avg_rating','>=',$average_ratting[0]);
                        $q->has('avg_rating','<=',$average_ratting[1]);
                    });
                }

                $user_list = $query->orderBy('user_id', 'DESC')->paginate(10)->toArray();
            }
            else
            {
                return response()->json([
                    'status'    => false,
                    'message'   => 'filter parameter is required',
                    'data'    => new ArrayObject,
                ], 200);
            }

        }
        else{
            
            $list = 'All';
            $user_list = User::withCount('complate_ride','cancel_ride','total_review')
            ->withCount([
                'avg_rating' => function ($query) {
                    $query->select(DB::raw('ROUND(coalesce(avg(ratting),0),1)'));
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
                'status'    => false,
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
                'status'    => false,
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
