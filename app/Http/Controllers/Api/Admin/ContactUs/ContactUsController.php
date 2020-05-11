<?php

namespace App\Http\Controllers\Api\Admin\ContactUs;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\PanelRepository;
use App\Http\Requests\Api\Admin\ContactUs\DeleteContactUsRequest;
use App\Http\Requests\Api\Admin\ContactUs\GetContactUsListRequest;
use App\Http\Requests\Api\Admin\ContactUs\ViewContactUsMessageRequest;

class ContactUsController extends Controller
{
    protected $panel;

    public function __construct()
    {
        $this->panel = new PanelRepository;
    }

    //  get contact us list 
    public function get_contact_us_list(GetContactUsListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->get_contact_us_list($request);
    }

    //  view contact us message 
    public function view_contact_us_message(ViewContactUsMessageRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->view_contact_us_message($request);
    }

    // delete contact us 
    public function delete_contact_us(DeleteContactUsRequest $request, $id)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->delete_contact_us($request, $id);

    }
}
