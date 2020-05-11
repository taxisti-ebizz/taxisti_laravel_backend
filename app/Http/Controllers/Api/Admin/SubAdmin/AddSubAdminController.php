<?php

namespace App\Http\Controllers\Api\Admin\SubAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\PanelRepository;
use App\Http\Requests\Api\Admin\SubAdmin\AddSubAdminRequest;

class AddSubAdminController extends Controller
{
    protected $panel;

    public function __construct()
    {
        $this->panel = new PanelRepository;
    }

    //  add sub_admin  
    public function add_sub_admin(AddSubAdminRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->add_sub_admin($request);
    }

}
