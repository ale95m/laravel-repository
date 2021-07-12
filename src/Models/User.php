<?php

namespace Easy\Models;


use Easy\Interfaces\ILogable;
use Easy\Traits\HasLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected string|array $authField = 'email';
    protected string $authPasswordField = 'password';


//    #region Relations
//    public function roles()
//    {
//        return $this->belongsToMany(Role::class);
//    }
//    #endregion

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    function getToken(): string
    {
        return $this->createToken(env('APP_NAME'))->accessToken;
    }

    function removeToken(): ?bool
    {
        return $this->token()->delete();
    }


    public function getAuthField(): array
    {
        return is_array($this->authField) ? $this->authField : [$this->authField];
    }

    public function getAuthPasswordField(): string
    {
        return $this->authPasswordField;
    }

}
