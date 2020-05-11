<?php

namespace App\Http\Controllers\Api\Admin\AdminProfile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\PanelRepository;
use App\Http\Requests\Api\Admin\Panel\UpdateAdminProfileRequest;

class AdminProfileController extends Controller
{
    protected $panel;

    public function __construct()
    {
        $this->panel = new PanelRepository;
    }


    //  update  admin profile 
    public function update_admin_profile(UpdateAdminProfileRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->update_admin_profile($request);
    }

}
