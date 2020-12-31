<?php

declare(strict_types=1);

namespace horstoeko\slimapp\security;

use Illuminate\Database\Capsule\Manager as Capsule;
use horstoeko\slimapp\dbtables\User as UserTable;
use SlimSession\Helper as SessionHelper;

class SlimAppLoginManager
{
    protected const SESSION_LOGINFLAG = 'login.isloggedin';
    protected const SESSION_LOGINUSERID = 'login.user.id';
    protected const SESSION_LOGINUSERNAME = 'login.user.username';
    protected const SESSION_LOGINFIRSTNAME = 'login.user.firstname';
    protected const SESSION_LOGINLASTNAME = 'login.user.lastname';

    /**
     * @var \Illuminate\Database\Capsule\Manager
     */
    protected $capsule;

    /**
     * @var \SlimSession\Helper
     */
    protected $sessionHelper;

    /**
     * Constructor
     *
     * @param Capsule $capsule
     */
    public function __construct(Capsule $capsule, SessionHelper $sessionHelper)
    {
        $this->capsule = $capsule;
        $this->sessionHelper = $sessionHelper;
    }

    /**
     * Perform login
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function loginUser(string $username, string $password): bool
    {
        $userData = UserTable::where("username", "=", $username)->andWhere("password", "=", $password)->first();

        if (!$userData) {
            return false;
        }

        $this->sessionHelper->set(self::SESSION_LOGINFLAG, true);
        $this->sessionHelper->set(self::SESSION_LOGINUSERID, $userData->id);
        $this->sessionHelper->set(self::SESSION_LOGINUSERNAME, $userData->username);
        $this->sessionHelper->set(self::SESSION_LOGINFIRSTNAME, $userData->firstname);
        $this->sessionHelper->set(self::SESSION_LOGINLASTNAME, $userData->lastname);

        return true;
    }

    /**
     * Logout current user
     *
     * @return void
     */
    public function logoutUser(): void
    {
        $this->sessionHelper->delete(self::SESSION_LOGINFLAG);
        $this->sessionHelper->delete(self::SESSION_LOGINUSERID);
        $this->sessionHelper->delete(self::SESSION_LOGINUSERNAME);
        $this->sessionHelper->delete(self::SESSION_LOGINFIRSTNAME);
        $this->sessionHelper->delete(self::SESSION_LOGINLASTNAME);
    }

    /**
     * Returns true if a user is logged in in the current session
     *
     * @return boolean
     */
    public function isSignedIn(): bool
    {
        return $this->sessionHelper->get(self::SESSION_LOGINFLAG, false);
    }


    /**
     * Get the information about a signed-in user
     *
     * @return integer
     */
    public function signedInUserId(): int
    {
        return $this->sessionHelper->get(self::SESSION_LOGINUSERID, -1);
    }

    /**
     * Get the information about a signed-in user
     *
     * @return string
     */
    public function signedInUserName(): string
    {
        return $this->sessionHelper->get(self::SESSION_LOGINUSERNAME, "");
    }

    /**
     * Get the information about a signed-in user
     *
     * @return string
     */
    public function signedInUserFirstname(): string
    {
        return $this->sessionHelper->get(self::SESSION_LOGINFIRSTNAME, "");
    }

    /**
     * Get the information about a signed-in user
     *
     * @return string
     */
    public function signedInUserLastname(): string
    {
        return $this->sessionHelper->get(self::SESSION_LOGINLASTNAME, "");
    }
}
