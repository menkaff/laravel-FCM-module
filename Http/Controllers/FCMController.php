<?php

namespace Modules\FCM\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\FCM\Service\FcmTokenService;
use Validator;

class FCMController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'token' => 'required',
            'device_id' => 'required']);

        if ($validator->fails()) {
            return responseError($validator->errors());
        }

        $user = Auth::user();
        $user_type = get_class($user);
        $fcm_token_service = new FcmTokenService;
        $result = $fcm_token_service->store([
            'user_id' => $user->id,
            'user_type' => $user_type,
            'token' => $request->token,
            'device_id' => $request->device_id]);
        if ($result['is_successful']) {
            return responseOk(true);
        } else {
            return responseError($result['message']);
        }
    }
}
