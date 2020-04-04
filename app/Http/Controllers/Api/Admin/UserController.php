<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\UserRepository;
use App\Http\Requests\Api\Admin\User\GetUserListRequest;

class UserController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = new UserRepository;
    }

    protected function get_user_list(GetUserListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'success'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 400);
        }   

        return $this->user->get_user_list($request);
    }
}
