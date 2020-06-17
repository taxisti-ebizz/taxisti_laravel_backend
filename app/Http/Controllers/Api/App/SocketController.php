<?php

namespace App\Http\Controllers\Api\App;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\App\SocketRepository;
use App\Http\Requests\Api\App\Socket\GetDriverSocketRequest;
use App\Http\Requests\Api\App\Socket\RequestRideSocketRequest;
use App\Http\Requests\Api\App\Socket\SendPushNotificationSocketRequest;

class SocketController extends Controller
{
    protected $socket;

    public function __construct()
    {
        $this->socket = new SocketRepository;
    }

    // get driver 
    protected function get_driver(GetDriverSocketRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->socket->get_driver($request);

    }

    // request ride 
    protected function request_ride(RequestRideSocketRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->socket->request_ride($request);

    }

    // send push notification
    protected function send_push_notification(SendPushNotificationSocketRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->socket->send_push_notification($request);

    }
}
