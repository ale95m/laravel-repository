<?php

namespace Easy\Http\Controllers;

use Easy\Exceptions\EasyException;
use Easy\Interfaces\IAuthenticable;
use Easy\Interfaces\IAuthenticableOAuth;
use Easy\Http\Responses\SendResponse;
use App\Http\Controllers\Controller;
use Easy\Repositories\LogRepository;
use GrahamCampbell\ResultType\Success;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $user_model = config('auth.providers.users.model', 'App\Models\User');
        if (!is_subclass_of($user_model, IAuthenticable::class)) {
            throw new \Exception(trans('easy::exceptions.auth.user_model_is_nut_authenticable', ['model' => $user_model]));
        }
        /** @var User $user */
        $user = new $user_model();
        $password_field = $user->getAuthPasswordField();
        $auth_fields = $user->getAuthField();
        if (!is_array($auth_fields)) {
            $auth_fields = [$auth_fields];
        }
        $current_auth_field = $auth_fields[0];
        foreach ($auth_fields as $auth_field) {
            if (array_key_exists($auth_field, $request->all())) {
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
            /** @var Model $user */
            $user = Auth::user();
            LogRepository::createLog('login', get_class($user), null, null, $user::class, $user->getKey());
            if (is_subclass_of($user, IAuthenticableOAuth::class)) {
                $token = $user->getToken();
                return SendResponse::successData($user->load(config('easy.auth_user_relations', [])), $token);
            } else {
                return SendResponse::successData($user->load(config('easy.auth_user_relations', [])));
            }
        } else {
            return SendResponse::error(trans('easy::messages.auth.invalid_credentials'), 401);
        }
    }

    function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!is_subclass_of($user, IAuthenticable::class)) {
            throw new \Exception(trans('easy::exceptions.auth.user_model_is_nut_authenticable', ['model' => $user_model]));
        }
        if (is_subclass_of($user, IAuthenticableOAuth::class)) {
            $user->removeToken();
        }else{
            Auth::logout();
        }
        LogRepository::createLog('logout', get_class($user), null, null, $user::class, $user->getKey());
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
            if (!is_subclass_of($user, IAuthenticable::class)) {
                throw new \Exception(trans('easy::exceptions.auth.user_model_is_nut_authenticable', ['model' => $user_model]));
            }
            $password_field = $user->getAuthPasswordField();
            $user->$password_field = bcrypt($password);
            $user->save();
        });

        if ($reset_password_status == Password::INVALID_TOKEN) {
            return SendResponse::error(trans('easy::messages.auth.invalid_token'));
        }

        return SendResponse::success();
    }
}
