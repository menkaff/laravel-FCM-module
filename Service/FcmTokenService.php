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
            return $query->where('user_id', $params['user_id']);
        })
            ->when(isset($params['user_type']), function ($query) use ($params) {
                return $query->where('user_type', $params['user_type']);
            })
            ->when(isset($params['token']), function ($query) use ($params) {
                return $query->where('token', $params['token']);
            })
            ->when(isset($params['device_id']), function ($query) use ($params) {
                return $query->where('device_id', $params['device_id']);
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

    public function push_notification($user_type, $user_id, $data)
    {
        $inputs = [
            'user_type' => $user_type,
            'user_id' => $user_id,
            'data' => $data,
        ];
        $validator = Validator::make($inputs, [
            'user_type' => 'required|exists:fcm_token',
            'user_id' => 'required|exists:fcm_token']);

        if ($validator->fails()) {
            return responseError($validator->errors());
        }

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder($data['builder']);
        $notificationBuilder->setBody($data['body'])
            ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData($data['added_data']);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        // $tokens = FcmToken::get()->pluck('token')->toArray();
        $tokens = FcmToken::where(['user_type' => $user_type, 'user_id' => $user_id])->pluck('token')->toArray();
        if ($tokens) {
            $downstreamResponse = FCM::sendTo($tokens, $option, $notification, $data);

            // return Array - you must remove all this tokens in your database
            $tokens_to_delete = $downstreamResponse->tokensToDelete();
            FcmToken::whereIn('token', $tokens_to_delete)->delete();

            // return Array (key:token, value:error) - in production you should remove from your database the tokens present in this array
            $toekens_with_error = $downstreamResponse->tokensWithError();
            FcmToken::whereIn('token', $toekens_with_error)->delete();

            // return Array (key : oldToken, value : new token - you must change the token in your database)
            $tokens_to_modify = $downstreamResponse->tokensToModify();
            foreach ($tokens_to_modify as $key => $value) {
                FcmToken::where('token', $key)->update(['token' => $value]);
            }

            // return Array - you should try to resend the message to the tokens in the array
            $tokens_to_retry = $downstreamResponse->tokensToRetry();

            if ($tokens_to_retry) {
                $downstreamResponse = FCM::sendTo($tokens_to_retry, $option, $notification, $data);
            }

            return serviceOk([
                'success' => $downstreamResponse->numberSuccess(),
                'fail' => $downstreamResponse->numberFailure(),
                'modify' => $downstreamResponse->numberModification(),
            ]);

        } else {
            return serviceError(false);
        }

    }

}
