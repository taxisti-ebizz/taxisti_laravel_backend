<?php

namespace App\Http\Controllers\Api\Admin\Notification;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\UserRepository;
use App\Repositories\Api\Admin\PanelRepository;
use App\Http\Requests\Api\Admin\Panel\SendNotificationRequest;
use App\Http\Requests\Api\Admin\Panel\GetSpecificUserListRequest;

class NotificationController extends Controller
{
    protected $panel;

    public function __construct()
    {
        $this->panel = new PanelRepository;
        $this->user = new UserRepository;
    }

    //  send notification 
    public function send_notification(SendNotificationRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->send_notification($request);
    }

    // get specific user list
    public function get_specific_user_list(GetSpecificUserListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->user->get_specific_user_list($request);
    }

}
