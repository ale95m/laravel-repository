<?php


namespace Easy\Traits;

use Laravel\Passport\HasApiTokens;

trait IsAuthenticableOAuth
{
    use IsAuthenticable, HasApiTokens;

    function getToken(): string
    {
        return $this->createToken(env('APP_NAME'))->accessToken;
    }

    function removeToken(): ?bool
    {
        return $this->token()->delete();
    }
}
