<?php

namespace App\Http\Controllers\Api\App\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\Auth\AppRegisterRequest;
use App\Repositories\Api\App\Auth\AppRegistorRepository;

class AppRegisterController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = new AppRegistorRepository;
    }

    protected function create(AppRegisterRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->user->create($request);

    }
}
