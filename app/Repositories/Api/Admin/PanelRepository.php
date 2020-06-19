<?php

namespace App\Repositories\Api\Admin;

use ArrayObject;
use App\Models\User;
use App\Models\Driver;
use App\Models\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PanelRepository extends Controller
{
    // get promotion list
    public function get_promotion_list($request)
    {
        if($request['type'] == 'filter')
        {
            if(isset($request['filter']))
            {
                
                $filter = json_decode($request['filter']);
                $query = [];

                $list = 'Filter';
      
                $query = DB::table('taxi_promotion');
       

                if(!empty($filter->code)) // code filter
                {
                    $query->where('code', 'LIKE', '%'.$filter->code.'%');
                }
                
                if(!empty($filter->type)) // type filter 
                {
                    $type = explode(',',$filter->type);
                    if(count($type) > 1)
                    {
                        $query->whereBetween('type',$type);
                    }
                    else
                    {
                        $query->where('type',$type[0]);
                    }
                }

                if(!empty($filter->user_limit)) // user_limit filter 
                {
                    $user_limit = explode('-',$filter->user_limit);
                    $query->whereBetween('user_limit',$user_limit);
                }

                if(!empty($filter->start_date)) // start_date filter 
                {
                    $start_date = explode(' ',$filter->start_date);
                    $query->whereBetween('start_date',$start_date);
                }

                if(!empty($filter->end_date)) // end_date filter 
                {
                    $end_date = explode(' ',$filter->end_date);
                    $query->whereBetween('end_date',$end_date);
                }
                

                $promotion_list = $query->orderBy('id', 'DESC')->paginate(10)->toArray();
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
        else
        {
            $list = 'All';

            $promotion_list = DB::table('taxi_promotion')->orderByRaw('id DESC')->paginate(10)->toArray();

        }    

        if($promotion_list['data'])
        {
            $lists = [];
            foreach ($promotion_list['data'] as $promotion) {
                
                $promotion->promo_image = $promotion->promo_image != "" ?  env('AWS_S3_URL') . $promotion->promo_image : '';
                $lists[] = $promotion;
            }
            
            $promotion_list['data'] = $lists;

            return response()->json([
                'status'    => true,
                'message'   => $list.' Promotion list', 
                'data'    => $promotion_list,
            ], 200);

        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }        
    }

    // update promotion detail
    public function update_promotion_detail($request)
    {

        $input = $request->except(['id']);
        $input['updated_at'] = date('Y-m-d H:i:s');

        // promo_image handling 
        if ($request->file('promo_image')) {

            // delete promo_image
            $promotion = DB::table('taxi_promotion')->where('id',$request['id'])->first();
            Storage::disk('s3')->exists($promotion->promo_image) ? Storage::disk('s3')->delete($promotion->promo_image) : '';

            $promo_image = $request->file('promo_image');
            $imageName = 'uploads/promo_image/' . time() . '.' . $promo_image->getClientOriginalExtension();
            $img = Storage::disk('s3')->put($imageName, file_get_contents($promo_image), 'public');
            $input['promo_image'] = $imageName;
        }

      
        $promotion = DB::table('taxi_promotion')
            ->where('id',$request['id'])
            ->update($input);

        return response()->json([
            'status'    => true,
            'message'   => 'Promotion updated', 
            'data'    => array(),
        ], 200);

    }

    // delete promotion
    public function delete_promotion($request, $id)
    {
        // delete promo_image
        $promotion = DB::table('taxi_promotion')->where('id',$id)->first();
        Storage::disk('s3')->exists($promotion->promo_image) ? Storage::disk('s3')->delete($promotion->promo_image) : '';

        DB::table('taxi_promotion')->where('id',$id)->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Promotion deleted', 
            'data'    => array(),
        ], 200);

    }

    // add promotion
    public function add_promotion($request)
    {

        $input = $request->all();
        $input['created_at'] = date('Y-m-d H:i:s');

        // promo_image handling 
        if ($request->file('promo_image')) {

            $promo_image = $request->file('promo_image');
            $imageName = 'uploads/promo_image/' . time() . '.' . $promo_image->getClientOriginalExtension();
            $img = Storage::disk('s3')->put($imageName, file_get_contents($promo_image), 'public');
            $input['promo_image'] = $imageName;
        }

        $promotion = DB::table('taxi_promotion')->insert($input);

        return response()->json([
            'status'    => true,
            'message'   => 'Promotion add successfully', 
            'data'    => array(),
        ], 200);

    }

    // get user promotion list
    public function get_user_promotion_list($request)
    {
        if($request['type'] == 'filter')
        {
            if(isset($request['filter']))
            {
                
                $filter = json_decode($request['filter']);
                $query = [];

                $list = 'Filter all';
      
                $query = DB::table('taxi_user_promotion')
                ->select('taxi_user_promotion.id','taxi_user_promotion.user_id',
                    'taxi_user_promotion.redeem','taxi_promotion.description','taxi_user_promotion.created_at', 
                    DB::raw('CONCAT(taxi_users.first_name," ",taxi_users.last_name) as name'),
                        'taxi_users.mobile_no','taxi_users.profile_pic'
                    )
                ->join('taxi_users', 'taxi_user_promotion.user_id', '=', 'taxi_users.user_id')
                ->join('taxi_promotion', 'taxi_user_promotion.promotion_id', '=', 'taxi_promotion.id');
       

                if(!empty($filter->username)) // username filter
                {
                    $username = explode(' ',$filter->username);
                    if(count($username) > 1)
                    {
                        $query->where('taxi_users.first_name', 'LIKE', '%'.$username[0].'%')->orWhere('taxi_users.last_name', 'LIKE', '%'.$username[1].'%');
                    }
                    else
                    {
                        $query->where('taxi_users.first_name', 'LIKE', '%'.$filter->username.'%')->orWhere('taxi_users.last_name', 'LIKE', '%'.$filter->username.'%');
                    }
                }


                if(!empty($filter->mobile)) // mobile filter 
                {
                    $query->where('taxi_users.mobile_no', 'LIKE', '%'.$filter->mobile.'%');
                }

                if(!empty($filter->description)) // description filter 
                {
                    $query->where('taxi_promotion.description', 'LIKE', '%'.$filter->description.'%');
                }
                
                if(!empty($filter->apply_date)) // apply_date filter 
                {
                    $apply_date = explode(' ',$filter->apply_date);
                    $query->whereBetween('taxi_user_promotion.created_at',$apply_date);
                }
                

                $user_promotion_list = $query->orderBy('taxi_user_promotion.id', 'DESC')->paginate(10)->toArray();
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
        else
        {
            $list = 'All';

            $user_promotion_list = DB::table('taxi_user_promotion')
            ->select('taxi_user_promotion.id','taxi_user_promotion.user_id',
                'taxi_user_promotion.redeem','taxi_promotion.description','taxi_user_promotion.created_at', 
                DB::raw('CONCAT(taxi_users.first_name," ",taxi_users.last_name) as name'),
                    'taxi_users.mobile_no','taxi_users.profile_pic'
                )
            ->join('taxi_users', 'taxi_user_promotion.user_id', '=', 'taxi_users.user_id')
            ->join('taxi_promotion', 'taxi_user_promotion.promotion_id', '=', 'taxi_promotion.id')
            ->orderByRaw('taxi_user_promotion.id DESC')
            ->paginate(10)->toArray();

        }

        if($user_promotion_list['data'])
        {
            $lists = [];
            foreach ($user_promotion_list['data'] as $promotion) {
                
                $promotion->profile_pic = $promotion->profile_pic != "" ?  env('AWS_S3_URL') . $promotion->profile_pic : '';
                $lists[] = $promotion;
            }
            
            $user_promotion_list['data'] = $lists;

            return response()->json([
                'status'    => true,
                'message'   => $list.' User promotion list', 
                'data'    => $user_promotion_list,
            ], 200);

        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }        
    }

    // redeem promotion
    public function redeem_promotion($request)
    {

        $input = $request->except(['id']);
        $input['redeem'] = 1; 
        $input['updated_at'] = date('Y-m-d H:i:s');

        $redeem = DB::table('taxi_user_promotion')->where('id',$request['id'])->update($input);

        $promotion = DB::table('taxi_user_promotion')
        ->select('taxi_promotion.code','taxi_users.device_type','taxi_users.device_token','taxi_users.user_id')
        ->join('taxi_users', 'taxi_user_promotion.user_id', '=', 'taxi_users.user_id')
        ->join('taxi_promotion', 'taxi_user_promotion.promotion_id', '=', 'taxi_promotion.id')
        ->where('taxi_user_promotion.id',$request['id'])
        ->first();

        $msg = "The offer for ".$promotion->code." has been successfully served";
        $noti_type = "promotion_redeem";
        $this->sendNotiToUser($promotion->user_id,$promotion->device_token,$promotion->device_type,$msg,$noti_type);

        return response()->json([
            'status'    => true,
            'message'   => 'Promotion redeem', 
            'data'    => array(),
        ], 200);

    }

    // get options
    public function get_options($request)
    {

        $options = DB::table('taxi_option')->get();

        return response()->json([
            'status'    => true,
            'message'   => 'Options list', 
            'data'    => $options,
        ], 200);

    }

    // update options
    public function update_options($request)
    {
        $input = $request->all();
        foreach ($input as $key => $value) {
            $options = DB::table('taxi_option')->where('option_name',$key)->update(['option_value' => $value]);
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Options updated', 
            'data'    => array(),
        ], 200);

    }

    // get contact us list
    public function get_contact_us_list($request)
    {
        if($request['type'] == 'filter')
        {
            if(isset($request['filter']))
            {
                
                $filter = json_decode($request['filter']);
                $query = [];

                $list = 'Filter all';
      
                $query = DB::table('taxi_contact_us')
                ->select('taxi_contact_us.*',
                    DB::raw('CONCAT(taxi_users.first_name," ",taxi_users.last_name) as name'),
                        'taxi_users.mobile_no'
                    )
                ->join('taxi_users', 'taxi_contact_us.user_id', '=', 'taxi_users.user_id');
       

                if(!empty($filter->username)) // username filter
                {
                    $username = explode(' ',$filter->username);
                    if(count($username) > 1)
                    {
                        $query->where('taxi_users.first_name', 'LIKE', '%'.$username[0].'%')->orWhere('taxi_users.last_name', 'LIKE', '%'.$username[1].'%');
                    }
                    else
                    {
                        $query->where('taxi_users.first_name', 'LIKE', '%'.$filter->username.'%')->orWhere('taxi_users.last_name', 'LIKE', '%'.$filter->username.'%');
                    }
                }

                if(!empty($filter->message)) // message filter 
                {
                    $query->where('taxi_contact_us.message', 'LIKE', '%'.$filter->message.'%');
                }

                if(!empty($filter->status)) // status filter 
                {
                    $status = explode(',',$filter->status);
                    if(count($status) > 1)
                    {
                        $query->whereBetween('taxi_contact_us.status',$status);
                    }
                    else
                    {
                        $query->where('taxi_contact_us.status',$status[0]);
                    }

                }
                
                if(!empty($filter->date)) // date filter 
                {
                    $date = explode(' ',$filter->date);
                    $query->whereBetween('taxi_contact_us.created_date',$date);
                }

                $contact_us_list = $query->orderBy('taxi_contact_us.id', 'DESC')->paginate(10)->toArray();
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
        else
        {
            $list = 'All';

            $contact_us_list = DB::table('taxi_contact_us')
                ->select('taxi_contact_us.*',
                    DB::raw('CONCAT(taxi_users.first_name," ",taxi_users.last_name) as name'),
                        'taxi_users.mobile_no'
                    )
                ->join('taxi_users', 'taxi_contact_us.user_id', '=', 'taxi_users.user_id')
                ->orderByRaw('taxi_contact_us.id DESC')
                ->paginate(10)->toArray();

        }

        if($contact_us_list['data']) {

            return response()->json([
                'status'    => true,
                'message'   => $list.' Contact us list', 
                'data'    => $contact_us_list,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }        

    }

    // view contact us message
    public function view_contact_us_message($request)
    {
        $contact_us = DB::table('taxi_contact_us')->where('id',$request['id'])->first();
        return response()->json([
            'status'    => true,
            'message'   => 'Contact us detail', 
            'data'    => $contact_us,
        ], 200);

    }

    // delete contact us
    public function delete_contact_us($request,$id)
    {
        $contact_us = DB::table('taxi_contact_us')->where('id',$id)->delete();
        return response()->json([
            'status'    => true,
            'message'   => 'Contact us deleted', 
            'data'    => array(),
        ], 200);

    }
        
    // send notification
    public function send_notification($request)
    {
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        $msg = $request['notification_message'];
        $type = 'admin_notification';
        $user_id = explode(",",$request['user_id']);
        $user_data = [];

        if ($request['to'] == 'user') {
            $user_data = User::select('user_id','device_type','device_token')->whereIn('user_id',$user_id)->get();
        }
        elseif ($request['to'] == 'allUser') {
            $user_data = User::select('user_id','device_type','device_token')->where('user_type',0)->get();

        }
        elseif ($request['to'] == 'allDriver') {
            $user_data = User::select('user_id','device_type','device_token')->where('user_type',1)->get();

        }
        elseif ($request['to'] == 'all') {
            $user_data = User::select('user_id','device_type','device_token')->where('verify',1)->get();

        }

        foreach ($user_data as   $user) {
            $send = $this->sendNotiToUser($user->user_id,$user->device_token,$user->device_type,$msg,$type);
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Notification send sccessfully', 
            'data'    => array(),
        ], 200);

    }

    //  get page list 
    public function get_page_list($request)
    {
        
        $page_list = DB::table('taxi_pages')->orderByRaw('id DESC')->paginate(10)->toArray();

        if($page_list['data'])
        {
            return response()->json([
                'status'    => true,
                'message'   => 'Page list', 
                'data'    => $page_list,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }       
    }

    //  add page 
    public function add_page($request)
    {
        $input = $request->all();
        $input['created_date'] = date('Y-m-d H:i:s');
        $page = DB::table('taxi_pages')->insert($input);

        return response()->json([
            'status'    => true,
            'message'   => 'Page add successfully', 
            'data'    => array(),
        ], 200);

    }

    //  edit page 
    public function edit_page($request)
    {
        $input = $request->except(['id']);
        $page = DB::table('taxi_pages')->where('id',$request['id'])->update($input);

        return response()->json([
            'status'    => true,
            'message'   => 'Page edit successfully', 
            'data'    => array(),
        ], 200);

    }

    //  delete page 
    public function delete_page($request, $id)
    {
        $page = DB::table('taxi_pages')->where('id',$id)->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Page delete successfully', 
            'data'    => array(),
        ], 200);

    }

    //  get sub admin list 
    public function get_sub_admin_list($request)
    {
        if($request['type'] == 'filter')
        {
            if(isset($request['filter']))
            {
                
                $filter = json_decode($request['filter']);
                $query = [];

                $list = 'Filter all';
      
                $query = DB::table('taxi_admin')->select('user_id','name','email_id','mobile_no','type','status','lastupdated_date','lastupdated_time')->where('type',1);

                if(!empty($filter->name)) // name filter
                {
                    $query->where('name', 'LIKE', '%'.$filter->name.'%');
                }

                if(!empty($filter->email_id)) // email_id filter 
                {
                    $query->where('email_id', 'LIKE', '%'.$filter->email_id.'%');
                }

                $sub_admin_list = $query->orderBy('user_id', 'DESC')->paginate(10)->toArray();
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
        else
        {
            $list = 'All';

            $sub_admin_list = DB::table('taxi_admin')
                ->select('user_id','name','email_id','mobile_no','type','status','lastupdated_date','lastupdated_time')
                ->where('type',1)
                ->orderByRaw('user_id DESC')
                ->paginate(10)->toArray();
        }

        if($sub_admin_list['data'])
        {
            return response()->json([
                'status'    => true,
                'message'   => $list.' Sub admin list', 
                'data'    => $sub_admin_list,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'    => false,
                'message'   => 'No data available', 
                'data'    => new ArrayObject,
            ], 200);
        }       
    }

    //  update sub admin status 
    public function update_sub_admin_status($request)
    {
        $input = $request->except(['user_id']);
        $input['lastupdated_date'] = date('Y-m-d');
        $input['lastupdated_time'] = date('H:i:s');

        $sub_admin_status = DB::table('taxi_admin')->where('user_id',$request['user_id'])->update($input);

        return response()->json([
            'status'    => true,
            'message'   => 'Status update successfully', 
            'data'    => array(),
        ], 200);
               
    }

    //  delete sub admin 
    public function delete_sub_admin($request, $id)
    {
        $page = DB::table('taxi_admin')->where('user_id',$id)->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Sub admin delete successfully', 
            'data'    => array(),
        ], 200);

    }

    //  delete sub admin 
    public function get_sub_admin($request)
    {
        $sub_admin = DB::table('taxi_admin')->where('user_id',$request['user_id'])->first();

        return response()->json([
            'status'    => true,
            'message'   => 'Sub admin Detail', 
            'data'    => $sub_admin,
        ], 200);

    }
    

    //  add sub_admin 
    public function add_sub_admin($request)
    {
        $input = $request->all();
        $input['password'] = md5($input['password']);
        $input['type'] = 1;
        $input['status'] = 1;
        
        $sub_admin = DB::table('taxi_admin')->insert($input);

        $body = "Your New email and password for Access Admin panel.<br><br> Your Email is: <strong>".$request['email_id']."</strong><br> Your Password is: <strong>".$request['password']."</strong><br><br>Please use this email and password for login.<br><br><br> Regards, <br> Taxisti Team.";

        Mail::send([], [], function ($message) use ($request, $body) {
            $message->to($request->email_id)
            ->from('noreply@ebizzdevelopment.com', 'Taxisti')
            ->subject('Taxisti admin panel credintial')
            ->setBody($body, 'text/html');
        });

        return response()->json([
            'status'    => true,
            'message'   => 'Sub admin add successfully', 
            'data'    => array(),
        ], 200);

    }

    //  update admin profile 
    public function update_admin_profile($request)
    {
        $input = $request->except(['user_id','password']);
        if( $request['password'] != '')
        {
            $input['password'] = md5($request['password']);
        }
        $input['lastupdated_date'] = date('Y-m-d');
        $input['lastupdated_time'] = date('H:i:s');

        $admin_profile = DB::table('taxi_admin')->where('user_id',$request['user_id'])->update($input);

        return response()->json([
            'status'    => true,
            'message'   => 'Admin profile update successfully', 
            'data'    => DB::table('taxi_admin')->where('user_id',$request['user_id'])->select('user_id','name','mobile_no','email_id')->first(),
        ], 200);
               
    }

    //  get dashboard data
    public function get_dashboard_data()
    {
        $dashboard_data = [];
        $dashboard_data['time'] = date('Y-m-d H:i:s');
        
        /*-----------------User data-----------*/
        $dashboard_data['cms_count'] = DB::table('taxi_pages')->count();
        $dashboard_data['users_count'] = User::where('user_type',0)->count();
        $dashboard_data['driver_count'] = User::where('user_type',1)->count();
        $dashboard_data['online_driver_count'] = $this->online_driver_count();

        /*------------------------------------ Requested Data-----------------------------------*/

        $dashboard_data['pending_ride'] = Request::where('status',0)->count();
        $dashboard_data['autocomplete_ride'] = Request::where('status',3)->where('is_canceled',1)->where('cancel_by',0)->count();
        $dashboard_data['completed_ride'] = Request::where('status',3)->where('ride_status',3)->count();
        $dashboard_data['fake_ride'] = Request::where('ride_status',4)->count();
        $dashboard_data['running_ride'] = Request::where('status',1)->count();
        $dashboard_data['canceled_ride'] = Request::where('status',3)->where('is_canceled',1)->whereIn('cancel_by',[1,2])->count();
        $dashboard_data['driver_not_available'] = DB::table('taxi_driver_notavailable')->join('taxi_users','taxi_users.user_id','taxi_driver_notavailable.rider_id')->count();

        /*-------------Get current week data-------------------*/
        $previous_week = strtotime("0 week +1 day");
        $start_week = strtotime("last saturday midnight",$previous_week);
        $end_week = strtotime("next friday",$start_week);
        $start_current_week = date('Y-m-d H:i:s',$start_week);
        $end_current_week = date("Y-m-d 23:59:00",$end_week);

        $dashboard_data['current_week_users_count'] = User::where('user_type',0)->whereBetween('created_date',[$start_current_week,$end_current_week])->count();
        $dashboard_data['current_week_week_driver_count'] = User::where('user_type',1)->whereBetween('created_date',[$start_current_week,$end_current_week])->count();
        $dashboard_data['current_week_pending_ride'] = Request::where('status',0)->whereBetween('created_date',[$start_current_week,$end_current_week])->count();
        $dashboard_data['current_week_autocomplete_ride'] = Request::where('status',3)->where('is_canceled',1)->where('cancel_by',0)->whereBetween('created_date',[$start_current_week,$end_current_week])->count();
        $dashboard_data['current_week_completed_ride'] = Request::where('status',3)->where('ride_status',3)->whereBetween('created_date',[$start_current_week,$end_current_week])->count();
        $dashboard_data['current_week_fake_ride'] = Request::where('ride_status',4)->whereBetween('created_date',[$start_current_week,$end_current_week])->count();
        $dashboard_data['current_week_running_ride'] = Request::where('status',1)->whereBetween('created_date',[$start_current_week,$end_current_week])->count();
        $dashboard_data['current_week_canceled_ride'] = Request::where('status',3)->where('is_canceled',1)->whereIn('cancel_by',[1,2])->whereBetween('created_date',[$start_current_week,$end_current_week])->count();
        $dashboard_data['current_week_driver_not_available'] = DB::table('taxi_driver_notavailable')->join('taxi_users','taxi_users.user_id','taxi_driver_notavailable.rider_id')->whereBetween('taxi_driver_notavailable.created_date',[$start_current_week,$end_current_week])->count();

       /*-------------Get Last week request data-----------------------*/
        $previous_week1 = strtotime("-1 week +1 day");
        $start_week = strtotime("last saturday midnight",$previous_week1);
        $end_week = strtotime("next friday",$start_week);
        $start_last_week = date('Y-m-d H:i:s',$start_week);
        $end_last_week = date("Y-m-d 23:59:00",$end_week);

        $dashboard_data['last_week_users_count'] = User::where('user_type',0)->whereBetween('created_date',[$start_last_week,$end_last_week])->count();
        $dashboard_data['last_week_driver_count'] = User::where('user_type',1)->whereBetween('created_date',[$start_last_week,$end_last_week])->count();
        $dashboard_data['last_week_pending_ride'] = Request::where('status',0)->whereBetween('created_date',[$start_last_week,$end_last_week])->count();
        $dashboard_data['last_week_autocomplete_ride'] = Request::where('status',3)->where('is_canceled',1)->where('cancel_by',0)->whereBetween('created_date',[$start_last_week,$end_last_week])->count();
        $dashboard_data['last_week_completed_ride'] = Request::where('status',3)->where('ride_status',3)->whereBetween('created_date',[$start_last_week,$end_last_week])->count();
        $dashboard_data['last_week_fake_ride'] = Request::where('ride_status',4)->whereBetween('created_date',[$start_last_week,$end_last_week])->count();
        $dashboard_data['last_week_running_ride'] = Request::where('status',1)->whereBetween('created_date',[$start_last_week,$end_last_week])->count();
        $dashboard_data['last_week_canceled_ride'] = Request::where('status',3)->where('is_canceled',1)->whereIn('cancel_by',[1,2])->whereBetween('created_date',[$start_last_week,$end_last_week])->count();
        $dashboard_data['last_week_driver_not_available'] = DB::table('taxi_driver_notavailable')->join('taxi_users','taxi_users.user_id','taxi_driver_notavailable.rider_id')->whereBetween('taxi_driver_notavailable.created_date',[$start_last_week,$end_last_week])->count();

        return response()->json([
            'status'    => true,
            'message'   => 'Dashboard data', 
            'data'    => $dashboard_data,
        ], 200);
    }


    // Sub function ===========================================

    // send notification to user
    public function sendNotiToUser($user_id,$device_token,$device_type,$msg,$type)
    {   
        $notificationmsg = 0;
        if($device_type=='A')
        {
            $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
    
            $fields = array(
                'to' => $device_token,
                //'notification' => array('title' => 'Notification from Taxisti','body'=>$msg),
                'data' => array('message' => $msg, 'type' => $type,'title' => 'Notification from Taxisti', 'body' => "success"),
            );
            
    
            $headers = array(
                /*'Authorization:key=AIzaSyAlN84WM8MaPgO_JPRKvLi1bFvWyI_DT1A', */
                'Authorization:key=AIzaSyBHZX8zi36hoodNoZLjrZxbgtTV9OwoyPw',
                'Content-Type:application/json'
            );		////AIzaSyD1kfwetZt8WINTkC65qwWW6eV9oj95cPA 
            $ch = curl_init();
    
            curl_setopt($ch, CURLOPT_URL, $path_to_firebase_cm); 
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    
    
            $result = curl_exec($ch);
            
            curl_close($ch);
    
            if($result)
            {
                $insertData = json_encode($fields['data']);
                
                $input['user_id'] = $user_id; 
                $input['message'] = $insertData; 
                $input['type'] = $type; 
                $input['datetime'] = date('Y-m-d H:i:s'); 

                DB::table('taxi_notification')->insert($input);
                $notificationmsg = '1';
            }
    
        }
        elseif($device_type=='I')
        {
            
            $apnsServer = 'ssl://gateway.push.apple.com:2195';
            $privateKeyPassword = '1';
            $message = $msg;
            $deviceToken = $device_token;
            $pushCertAndKeyPemFile = 'pushcert.pem';
            $stream = stream_context_create();
            stream_context_set_option($stream,'ssl','passphrase',$privateKeyPassword);
            stream_context_set_option($stream,'ssl','local_cert',$pushCertAndKeyPemFile);
    
            $connectionTimeout = 20;
            $connectionType = STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT;
            $connection = stream_socket_client($apnsServer,$errorNumber,$errorString,$connectionTimeout,$connectionType,$stream);
    
            if (!$connection){
                //echo "Failed to connect to the APNS server. Error no = $errorNumber<br/>";
                // exit;
            } 
            else {
               //echo "Successfully connected to the APNS. Processing...</br>";
            }
           // die;
            $messageBody['aps'] = array(
                'alert' => array(
                    'title' => "Notification from Taxisti",
                    'body' => $message
                ),
                "user_id" => $user_id,
                "type" => $type,
                "badge" => +1,
                "sound" => 'default'
            );
            $payload = json_encode($messageBody);
            $notification = chr(0) .pack('n', 32) .pack('H*', $deviceToken) .pack('n', strlen($payload)) .$payload;
            $wroteSuccessfully = fwrite($connection, $notification, strlen($notification));
            if (!$wroteSuccessfully){
                $result=0;
            }
            else {
                $result=1;
            }
            fclose($connection);
            if($result)
            {
                $datas = json_encode($messageBody['aps']);
                $datas='{"alert":{"title":"Notification from Taxisti","body":"'.$message.'"},"user_id":"'.$user_id.'","type":"admin_notification","badge":1,"sound":"default"}';
                
                $input['user_id'] = $user_id; 
                $input['message'] = $datas; 
                $input['type'] = $type; 
                $input['datetime'] = date('Y-m-d H:i:s'); 

                DB::table('taxi_notification')->insert($input);
                $notificationmsg = '1';
            }
        }
        
        return $notificationmsg;
    }

    // 
    public function online_driver_count()
    {
        $url="https://taxisti-8392c.firebaseio.com/userData1.json";

        $ch = curl_init();
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($ch, CURLOPT_URL,$url);
        // Execute
        $result=curl_exec($ch);
        // Closing
        curl_close($ch);

                    
        $ids = '';
        if(!empty($result))
        {
            $datas = json_decode($result);
            foreach ($datas as $key => $value) 
            {
                if($ids!='')
                {
                    $ids .= ',';
                }
                $ids .= $key;
            }
        }
        if($ids == '')
        {
            $ids = 0;
        }


        $driver_id = $ids;
        $driverArray = explode(',', $driver_id);

        $driver_count = Driver::select('taxi_driver_detail.*','taxi_users.*')
        ->leftJoin('taxi_users', 'taxi_driver_detail.driver_id', '=', 'taxi_users.user_id')
        ->whereIn('taxi_users.user_id', $driverArray)
        ->count();

        return $driver_count;
    }
}