<?php

namespace Easy\Interfaces;

interface IAuthenticable
{
    public function getAuthField(): array|string;

    public function getAuthPasswordField(): string;
}