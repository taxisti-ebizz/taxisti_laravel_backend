<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\UserRepository;
use App\Http\Requests\Api\Admin\User\DeleteUserRequest;
use App\Http\Requests\Api\Admin\User\GetUserListRequest;
use App\Http\Requests\Api\Admin\User\GetUserDetailRequest;
use App\Http\Requests\Api\Admin\User\EditUserDetailRequest;
use App\Http\Requests\Api\Admin\User\EditUserStatusRequest;
use App\Http\Requests\Api\Admin\User\GetRiderReviewstRequest;
use App\Http\Requests\Api\Admin\User\ViewRiderReviewstRequest;

class UserController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = new UserRepository;
    }

    // get user list
    protected function get_user_list(GetUserListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->user->get_user_list($request);
    }

    // get user detail
    protected function get_user_detail(GetUserDetailRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->user->get_user_detail($request);

    }
    
    // edit user detail
    protected function edit_user_detail(EditUserDetailRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->user->edit_user_detail($request);

    }

    // edit user status
    protected function edit_user_status(EditUserStatusRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->user->edit_user_status($request);

    }


    // delete user
    protected function delete_user(DeleteUserRequest $request, $user_id)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->user->delete_user($request,$user_id);
    }
    
    // get rider reviews
    protected function get_rider_reviews(GetRiderReviewstRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->user->get_rider_reviews($request);
    }

    // view rider reviews
    protected function view_rider_reviews(ViewRiderReviewstRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->user->view_rider_reviews($request);
    }
}
