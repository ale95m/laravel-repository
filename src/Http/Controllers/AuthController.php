<?php

namespace Easy\Http\Controllers;

use Easy\Exceptions\EasyException;
use Easy\Models\User;
use Easy\Http\Responses\SendResponse;
use App\Http\Controllers\Controller;
use Easy\Repositories\LogRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $user_model = config('easy.user_model');
        /** @var User $user */
        $user = new $user_model();
        $password_field = $user->getAuthPasswordField();
        $auth_fields = $user->getAuthField();
        $current_auth_field = $auth_fields[0];
        foreach ($auth_fields as $auth_field) {
            if (array_key_exists($auth_field, (array)$request)) {
                $current_auth_field = $auth_field;
                break;
            }
        }
        $request->validate([
            $current_auth_field => 'required|string',
            $password_field => 'required|string',
        ]);
        $credentials = request([$current_auth_field, $password_field]);
        if (Auth::attempt($credentials)) {
            /** @var User $user */
            $user = Auth::user();
            $token = $user->getToken();
            LogRepository::createLog('login',get_class($user),null,null,$user::class,$user->getKey());
            return SendResponse::success($token);
        } else {
            return SendResponse::error(trans('easy::messages.auth.invalid_credentials'), 401);
        }
    }

    function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->removeToken();
        LogRepository::createLog('logout',get_class($user),null,null,$user::class,$user->getKey());
        return SendResponse::success(trans('easy::messages.auth.logout'));
    }

    function unauthorized(): \Illuminate\Http\JsonResponse
    {
        return SendResponse::error(trans('easy::messages.auth.unauthorized'), 401);
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
        if (is_null($url)) {
            EasyException::throwException(trans('easy::exceptions.not_found.model'));
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
            $user->password = bcrypt($password);
            $user->save();
        });

        if ($reset_password_status == Password::INVALID_TOKEN) {
            return SendResponse::error(trans('easy::messages.auth.invalid_token'));
        }

        return SendResponse::success();
    }
}
