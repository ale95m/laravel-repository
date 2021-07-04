<?php

namespace Easy\Http\Controllers;

use Easy\Http\Responses\SendResponse;
use Illuminate\Http\Request;
use function PHPUnit\Framework\throwException;

class VerificationController extends \Illuminate\Routing\Controller
{
    public function verify($user, Request $request)
    {
        if (!$request->hasValidSignature()) {
            throw new \Exception(trans('easy::exceptions.not_found.url'));
        }
        $user_model = config('easy.user_model');
        $user_model = new $user_model();
        $user = $user_model->find($user);
        if (is_null($user)) {
            throw new \Exception(trans('easy::exceptions.not_found.model'));
        }
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
        return SendResponse::success();
    }

    public function resend()
    {
        $user = auth()->user();
        if ($user->hasVerifiedEmail()) {
            throw new \Exception(trans('easy::messages.email_verify.already_verify'));
        }
        $user->sendEmailVerificationNotification();
        return SendResponse::success(trans('easy::messages.email_verify.success'));
    }
}
