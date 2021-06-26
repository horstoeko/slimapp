<?php

declare(strict_types=1);

namespace horstoeko\slimapp\baseapp\models;

use horstoeko\slimapp\eloquent\ModelWithEncryption as Eloquent;

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
     * @inheritDoc
     */
    protected $fillable = [
        'username',
        'password',
        'firstname',
        'lastname',
        'email',
        'admin',
        'token',
    ];

    /**
     * @inheritDoc
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @inheritDoc
     */
    protected $encryptable = [
        'password',
        'firstname',
        'lastname',
        'email',
    ];
}
