<?php

declare(strict_types=1);

namespace horstoeko\slimapp\dbtables;

use Illuminate\Database\Eloquent\Model as Eloquent;

class User extends Eloquent
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "slimapp_users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'firstname',
        'lastname',
        'email',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
}
