<?php

namespace App\Http\Controllers\Api\App;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\App\AppCommonRepository;
use App\Http\Requests\Api\App\Common\AdminSettingRequest;
use App\Http\Requests\Api\App\Common\UpdateProfileRequest;

class AppCommonController extends Controller
{
    protected $appCommon;

    public function __construct()
    {
        $this->appCommon = new AppCommonRepository;
    }


    // update profile
    public function update_profile(UpdateProfileRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->update_profile($request);

    }

    // admin setting
    public function admin_setting(AdminSettingRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->admin_setting($request);

    }

    
}
