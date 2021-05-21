<?php

namespace Easy\Http\Controllers;

use Easy\Models\User;
use Easy\Http\Responses\SendResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
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

    function logout(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $user->removeToken();
        return SendResponse::success('logout');
    }

    function unauthorized()
    {
        return SendResponse::error('Unauthorized', 401);
    }

}
