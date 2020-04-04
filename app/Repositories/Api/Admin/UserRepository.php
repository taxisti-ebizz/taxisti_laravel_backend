<?php


namespace App\Repositories\Api\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;

class UserRepository extends Controller
{

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $request
     * @return \App\Models\User
     */
    public function get_user_list($request)
    {
        return response()->json([
            'success'    => true,
            'message'   => 'All user list', 
            'data'    => User::paginate(),
        ], 200);
    }

}   