<?php

namespace Easy\Models;


use Easy\Interfaces\ILogable;
use Easy\Traits\HasLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements ILogable
{
    use HasApiTokens, HasFactory, Notifiable, HasLogs;

    protected $authField = 'email';
    protected $authPasswordField = 'password';

    #region Login
    protected $logableAttributes = [
    ];
    protected $logableRelations = [
        'roles:name'
    ];
    #endregion

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

    function getToken()
    {
        return $this->createToken(env('APP_NAME'))->accessToken;
    }

    function removeToken()
    {
        return $this->token()->delete();
    }

//    /**
//     * @param string $role
//     * @return bool
//     */
//    public function hasRole(string $role): bool
//    {
//        return $this->roles()->where('name', $role)->exists();
//    }

    public function getAuthField()
    {
        return $this->authField;
    }
    public function getAuthPasswordField()
    {
        return $this->authPasswordField;
    }

}
