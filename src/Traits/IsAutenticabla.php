<?php


namespace Easy\Traits;


/**
 * @property array|string authField
 * @property string authPasswordField
 */
trait IsAutenticabla
{
    public function getAuthField(): array|string
    {
        return $this->authField ?? 'email';
    }

    public function getAuthPasswordField(): string
    {
        return $this->authPasswordField ?? 'password';
    }
}
