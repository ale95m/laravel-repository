<?php

namespace Easy\Interfaces;

interface IAuthenticableOAuth extends IAuthenticable
{
    function getToken(): string;

    function removeToken(): ?bool;
}
