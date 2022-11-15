<?php

namespace Easy\Http\Controllers;

use Easy\Exceptions\EasyException;
use Easy\Http\Responses\SendResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use function PHPUnit\Framework\throwException;

class VerificationController extends \Illuminate\Routing\Controller
{
    public function verify($user, Request $request)
    {
        if (!$request->hasValidSignature()) {
            throw new \Exception(trans('easy::exceptions.not_found.url'));
        }
        $user_model = config('auth.providers.users.model', 'App\Models\User');
        $user_model = new $user_model();
        $user = $user_model->findOrFail($user);
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
        return SendResponse::success();
    }

    public function resend()
    {
        $user = auth()->user();
        if ($user->hasVerifiedEmail()) {
            EasyException::throwException(trans('easy::messages.email_verify.already_verify'));
        }
        $user->sendEmailVerificationNotification();
        return SendResponse::success(trans('easy::messages.email_verify.success'));
    }
}
