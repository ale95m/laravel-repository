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
    public function login()
    {
        $user = new User();
        $password_field = $user->getAuthPasswordField();
        $auth_field = $user->getAuthField();
        if (Auth::attempt([$auth_field => request($auth_field), $password_field => request($password_field)])) {//validar que el usuario existe en la bd
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
