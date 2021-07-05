<?php

namespace Easy\Http\Controllers;

use Easy\Exceptions\EasyException;
use Easy\Models\User;
use Easy\Http\Responses\SendResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $user_model = config('easy.user_model');
        $user = new $user_model();
        $password_field = $user->getAuthPasswordField();
        $auth_field = $user->getAuthField();
        $request->validate([
            $auth_field => 'required|string',
            $password_field => 'required|string',
        ]);
        $credentials = request([$auth_field, $password_field]);
        if (Auth::attempt($credentials)) {
            /** @var User $user */
            $user = Auth::user();
            $token = $user->getToken();
            return SendResponse::success($token);
        } else {
            return SendResponse::error('Unauthorised', 401);
        }
    }

    function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->removeToken();
        return SendResponse::success('logout');
    }

    function unauthorized(): \Illuminate\Http\JsonResponse
    {
        return SendResponse::error('Unauthorized', 401);
    }

    public function forgot(): \Illuminate\Http\JsonResponse
    {
        $credentials = request()->validate(['email' => 'required|email']);
        Password::sendResetLink($credentials);
        return SendResponse::success();
    }

    public function restorePassword()
    {
        $credentials = request()->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);
        $email = $credentials['email'];
        $token = $credentials['token'];
        $url = config('easy.restore_password_route');
        if (is_null($url)){
            EasyException::throwException(trans('easy::exeptions.not_found.model'));
        }
        return redirect("$url?email=$email&token=$token");
    }

    public function reset(): \Illuminate\Http\JsonResponse
    {
        $credentials = request()->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed'
        ]);

        $reset_password_status = Password::reset($credentials, function ($user, $password) {
            $user->password = $password;
            $user->save();
        });

        if ($reset_password_status == Password::INVALID_TOKEN) {
            return SendResponse::error('Invalid token');
        }

        return SendResponse::success();
    }
}
