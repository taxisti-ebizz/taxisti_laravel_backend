<?php

namespace App\Http\Controllers\Api\Admin\Auth;

use App\Models\Request;
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

    // distance
    public function distance()
    {
        $distance = Request::get(['id','distance']);
        $data = [];
        foreach($distance as $value)
        {
            $dist = explode(' ',$value->distance);
            if(count($dist) > 1)
            {
                if($dist[1] != 'km')
                {
                    $update['distance'] = ((float)$dist[0]  / 1000.0);
                    $ok =  Request::where('id',$value->id)->update($update);
                    $data[]  = $ok;
                }
                else
                {
                    $update['distance'] = (float)$dist[0];
                    $ok =  Request::where('id',$value->id)->update($update);
                    $data[]  = $ok;
                    
                }

            }
        }
        $distance = $data;
        return $distance;
    }
}

