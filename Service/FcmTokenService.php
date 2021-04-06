<?php

namespace Modules\FCM\Service;

use FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Modules\FCM\Models\FcmToken;
use Validator;

class FcmTokenService
{
    public function index($params = null)
    {

        $fcm_tokens = FcmToken::when(isset($params['user_id']), function ($query) use ($params) {
            $query->where('user_id', $params['user_id']);
        })
            ->when(isset($params['user_type']), function ($query) use ($params) {
                $query->where('user_type', $params['user_type']);
            })
            ->when(isset($params['token']), function ($query) use ($params) {
                $query->where('token', $params['token']);
            })
            ->when(isset($params['device_id']), function ($query) use ($params) {
                $query->where('device_id', $params['device_id']);
            })
            ->when((isset($params['page']) && isset($params['per_page'])), function ($query) use ($params) {
                $query->skip($params['page'] * $params['per_page']);
                $query->take($params['per_page']);
                return $query;
            })
            ->get();

        return serviceOk($fcm_tokens);
    }

    public function store($params = null)
    {
        $validator = Validator::make($params, [
            'token' => 'required',
            'device_id' => 'required']);

        if ($validator->fails()) {
            return responseError($validator->errors());
        }

        $fcm_token = new FcmToken;
        $fcm_token->user_id = $params['user_id'];
        $fcm_token->user_type = $params['user_type'];
        $fcm_token->token = $params['token'];
        $fcm_token->device_id = $params['device_id'];
        $fcm_token->save();

        return serviceOk(true);
    }
    public function pushNotificationWithFirebase($user_type, $user_id,$notif_type_obj)
    {
        $user=$user_type::findOrFail($user_id);
        $user->notify( $notif_type_obj);
    }

    public function pushNotificationWithPushe($app_id, $user_id, $user_type, $data,$custom=['data'=>1])
    {
        $YOUR_AUTH_TOKEN = 'your-token';
        $YOUR_APP_ID = $app_id;

        $ch = curl_init('https://api.pushe.co/v2/messaging/notifications/');

        curl_setopt_array($ch, array(
            CURLOPT_POST => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Token " . $YOUR_AUTH_TOKEN,
            ),
        ));

        $deviceIds = FcmToken::where(['user_type' => $user_type, 'user_id' => $user_id])->pluck('device_id')->toArray();
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
            'app_ids' => $YOUR_APP_ID,
            'data' => $data,
            'custom_content' => $custom,
            'filters' => array(
                'device_id' => $deviceIds,
            )
        )));

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}
