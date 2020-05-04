<?php

namespace App\Http\Controllers\Api\App;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Api\App\AppCommonRepository;
use App\Http\Requests\Api\App\Common\LogoutRequest;
use App\Http\Requests\Api\App\Common\AddReviewRequest;
use App\Http\Requests\Api\App\Common\ContactUsRequest;
use App\Http\Requests\Api\App\Common\AutoLogoutRequest;
use App\Http\Requests\Api\App\Common\CheckLoginRequest;
use App\Http\Requests\Api\App\Common\CheckPhoneRequest;
use App\Http\Requests\Api\App\Common\GetCmsPageRequest;
use App\Http\Requests\Api\App\Common\GetRattingRequest;
use App\Http\Requests\Api\App\Common\AdminSettingRequest;
use App\Http\Requests\Api\App\Common\CheckPromotionStatus;
use App\Http\Requests\Api\App\Common\UpdateProfileRequest;
use App\Http\Requests\Api\App\Common\ApplyPromotionRequest;
use App\Http\Requests\Api\App\Common\GetRequestListRequest;
use App\Http\Requests\Api\App\Common\DeletePromotionRequest;
use App\Http\Requests\Api\App\Common\AddUserPromotionRequest;
use App\Http\Requests\Api\App\Common\GetRequestDetailRequest;
use App\Http\Requests\Api\App\Common\CheckPromotionStatusRequest;

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

    // admin setting
    public function add_user_promotion(AddUserPromotionRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->add_user_promotion($request);

    }

    // apply promotion
    public function apply_promotion(ApplyPromotionRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->apply_promotion($request);

    }

    // auto logout
    public function auto_logout(AutoLogoutRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->auto_logout($request);

    }

    // check phone
    public function check_phone(CheckPhoneRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->check_phone($request);

    }

    // check promotion status
    public function check_promotion_status(CheckPromotionStatusRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->check_promotion_status($request);

    }

    // check login
    public function check_login(CheckLoginRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->check_login($request);

    }

    // contact us
    public function contact_us(ContactUsRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->contact_us($request);

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

        return $this->appCommon->delete_promotion($request, $id);

    }

    // get cms page
    public function get_cms_page(GetCmsPageRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->get_cms_page($request);

    }

    // get ratting
    public function get_ratting(GetRattingRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->get_ratting($request);

    }

    // get request detail
    public function get_request_detail(GetRequestDetailRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->get_request_detail($request);

    }

    // logout
    public function logout(LogoutRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->logout($request);

    }

    // get request list
    public function get_request_list(GetRequestListRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->get_request_list($request);

    }
    
    // add review
    public function add_review(AddReviewRequest $request)
    {
        if ($request->validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'parameter invalid', 
                'errors'    => $request->validator->errors(),
            ], 200);
        }   

        return $this->appCommon->add_review($request);

    }
}
