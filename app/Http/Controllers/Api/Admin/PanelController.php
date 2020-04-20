<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\Admin\PanelRepository;
use App\Http\Requests\Api\Admin\Page\AddPageRequest;
use App\Http\Requests\Api\Admin\Page\EditPageRequest;
use App\Http\Requests\Api\Admin\Page\DeletePageRequest;
use App\Http\Requests\Api\Admin\Page\GetPageListRequest;
use App\Http\Requests\Api\Admin\Options\GetOptionsRequest;
use App\Http\Requests\Api\Admin\Options\UpdateOptionsRequest;
use App\Http\Requests\Api\Admin\Panel\SendNotificationRequest;
use App\Http\Requests\Api\Admin\Promotion\AddPromotionRequest;
use App\Http\Requests\Api\Admin\SubAdmin\DeleteSubAdminRequest;
use App\Http\Requests\Api\Admin\SubAdmin\GetSubAdminListRequest;
use App\Http\Requests\Api\Admin\ContactUs\DeleteContactUsRequest;
use App\Http\Requests\Api\Admin\Promotion\DeletePromotionRequest;
use App\Http\Requests\Api\Admin\Promotion\RedeemPromotionRequest;
use App\Http\Requests\Api\Admin\ContactUs\GetContactUsListRequest;
use App\Http\Requests\Api\Admin\Promotion\GetPromotionListRequest;
use App\Http\Requests\Api\Admin\SubAdmin\UpdateSubAdminStatusRequest;
use App\Http\Requests\Api\Admin\ContactUs\ViewContactUsMessageRequest;
use App\Http\Requests\Api\Admin\Promotion\GetUserPromotionListRequest;
use App\Http\Requests\Api\Admin\Promotion\UpdatePromotionDetailRequest;

class PanelController extends Controller
{
    protected $panel;

    public function __construct()
    {
        $this->panel = new PanelRepository;
    }

    // get promotion list
    public function get_promotion_list(GetPromotionListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->get_promotion_list($request);

    }

    // update promotion detail
    public function update_promotion_detail(UpdatePromotionDetailRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->update_promotion_detail($request);

    }

    // delete promotion 
    public function delete_promotion(DeletePromotionRequest $request, $id)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->delete_promotion($request, $id);

    }

    // add promotion 
    public function add_promotion(AddPromotionRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->add_promotion($request);

    }

    // get user promotion list
    public function get_user_promotion_list(GetUserPromotionListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->get_user_promotion_list($request);

    }

    //  redeem promotion 
    public function redeem_promotion(RedeemPromotionRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->redeem_promotion($request);

    }

    //  get options 
    public function get_options(GetOptionsRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->get_options($request);
    }

    //  update options 
    public function update_options(UpdateOptionsRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->update_options($request);
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

    //  get page list 
    public function get_page_list(GetPageListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->get_page_list($request);
    }

    //  add page  
    public function add_page(AddPageRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->add_page($request);
    }

    //  edit page  
    public function edit_page(EditPageRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->edit_page($request);
    }

    // delete page 
    public function delete_page(DeletePageRequest $request, $id)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->panel->delete_page($request, $id);

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
}
