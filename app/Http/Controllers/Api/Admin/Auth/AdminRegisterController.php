<?php

namespace App\Http\Controllers\Api\Admin\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\Auth\AdminRegisterRequest;
use App\Repositories\Api\Admin\Auth\AdminRegistorRepository;

class AdminRegisterController extends Controller
{
    protected $admin;

    public function __construct()
    {
        $this->admin = new AdminRegistorRepository;
    }

    protected function create(AdminRegisterRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->admin->create($request);

    }
}

