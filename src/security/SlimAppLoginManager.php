<?php

declare(strict_types=1);

namespace horstoeko\slimapp\security;

use horstoeko\slimapp\crypt\SlimAppQuickEncryption;
use Illuminate\Database\Capsule\Manager as Capsule;
use horstoeko\slimapp\baseapp\models\User as UserModel;
use SlimSession\Helper as SessionHelper;

class SlimAppLoginManager
{
    protected const SESSION_LOGINFLAG = 'login.isloggedin';
    protected const SESSION_LOGINUSERID = 'login.user.id';
    protected const SESSION_LOGINUSERNAME = 'login.user.username';
    protected const SESSION_LOGINFIRSTNAME = 'login.user.firstname';
    protected const SESSION_LOGINLASTNAME = 'login.user.lastname';
    protected const SESSION_LOGINADMIN = 'login.user.isadmin';
    protected const SESSION_LOGINEMAIL = 'login.user.email';

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
        if ($this->isSignedIn()) {
            $this->logoutUser();
        }

        $userData = UserModel::where("username", "=", $username)->first();

        if (!$userData) {
            return false;
        }

        if ($userData->password != $password) {
            return false;
        }

        $this->sessionHelper->set(self::SESSION_LOGINFLAG, true);
        $this->sessionHelper->set(self::SESSION_LOGINUSERID, $userData->id);
        $this->sessionHelper->set(self::SESSION_LOGINUSERNAME, $userData->username);
        $this->sessionHelper->set(self::SESSION_LOGINFIRSTNAME, $userData->firstname);
        $this->sessionHelper->set(self::SESSION_LOGINLASTNAME, $userData->lastname);
        $this->sessionHelper->set(self::SESSION_LOGINADMIN, $userData->admin);
        $this->sessionHelper->set(self::SESSION_LOGINEMAIL, $userData->email);

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
        $this->sessionHelper->delete(self::SESSION_LOGINADMIN);
        $this->sessionHelper->delete(self::SESSION_LOGINEMAIL);
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
     * Returns true if a user is logged in in the current session
     *
     * @return boolean
     */
    public function isAdminSignedIn(): bool
    {
        return $this->isSignedIn() && $this->sessionHelper->get(self::SESSION_LOGINADMIN, 0) == 1;
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

    /**
     * Get the information about a signed-in user
     *
     * @return string
     */
    public function signedInUserEmail(): string
    {
        return $this->sessionHelper->get(self::SESSION_LOGINEMAIL, "");
    }

    /**
     * Create a new user
     *
     * @param string $username
     * @param string $password
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @return integer
     */
    public function createUser(
        string $username,
        string $password,
        string $firstname,
        string $lastname,
        string $email
    ): int {
        $userData = UserModel::where("username", "=", $username)->first();

        if ($userData) {
            return (int)$userData->id;
        }

        $dbValues = [
            'username' => $username,
            'password' => $password,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
        ];

        UserModel::create($dbValues);

        $userData = UserModel::where("username", "=", $username)->first();

        return (int)$userData->id;
    }

    /**
     * Modify existing user
     *
     * @param string $username
     * @param string $password
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @return integer
     */
    public function modifyUser(
        string $username,
        string $password,
        string $firstname,
        string $lastname,
        string $email
    ): int {
        $userData = UserModel::where("username", "=", $username)->first();

        if (!$userData) {
            return -1;
        }

        $dbValues = [
            'password' => $password,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
        ];

        $userData->update($dbValues);

        return (int)$userData->id;
    }

    /**
     * Delete existing user (by username)
     *
     * @param string $username
     * @return integer
     */
    public function deleteUser(string $username): int
    {
        $userData = UserModel::where("username", "=", $username)->first();

        if (!$userData) {
            return -1;
        }

        $userData->delete();

        return (int)$userData->id;
    }
}
