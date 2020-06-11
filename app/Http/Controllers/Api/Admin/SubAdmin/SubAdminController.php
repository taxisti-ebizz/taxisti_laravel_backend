<?php

namespace App\Http\Controllers\Api\Admin\SubAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\PanelRepository;
use App\Http\Requests\Api\Admin\SubAdmin\GetSubAdminRequest;
use App\Http\Requests\Api\Admin\SubAdmin\DeleteSubAdminRequest;
use App\Http\Requests\Api\Admin\SubAdmin\GetSubAdminListRequest;
use App\Http\Requests\Api\Admin\SubAdmin\UpdateSubAdminStatusRequest;

class SubAdminController extends Controller
{
    protected $panel;

    public function __construct()
    {
        $this->panel = new PanelRepository;
    }

    //  get sub admin list 
    public function get_sub_admin_list(GetSubAdminListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->get_sub_admin_list($request);
    }

    //  update sub admin status 
    public function update_sub_admin_status(UpdateSubAdminStatusRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->update_sub_admin_status($request);
    }

    // delete sub admin 
    public function delete_sub_admin(DeleteSubAdminRequest $request, $id)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->delete_sub_admin($request, $id);

    }

    //  get sub admin 
    public function get_sub_admin(GetSubAdminRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->get_sub_admin($request);
    }
    

}
