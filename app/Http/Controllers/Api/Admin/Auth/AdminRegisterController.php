<?php

namespace App\Http\Controllers\Api\Admin\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\Auth\AdminRegisterRequest;
use App\Repositories\Api\Admin\Auth\AdminRegistorRepository;

class AdminRegisterController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = new AdminRegistorRepository;
    }

    protected function create(AdminRegisterRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'success'    => false,
                'message'   => 'Data is invalid.', 
                'errors'    => $request->validator->errors(),
            ], 400);
        }   

        return $this->user->create($request);

    }
}

