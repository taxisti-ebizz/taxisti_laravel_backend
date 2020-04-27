<?php

namespace App\Http\Controllers\Api\App\Auth;

use App\Models\User;
use App\Models\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Api\App\Auth\AppLoginRequest;
use App\Repositories\Api\App\Auth\AppLoginRepository;

class AppLoginController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = new AppLoginRepository;
    }

    public function login(AppLoginRequest $request)
    { 
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->user->login($request);
 
    }
}
